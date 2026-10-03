<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsCategories;

use Tests\Support\zcUnitTestCase;

/**
 * Covers the category structure maps that back the categories sidebox, the
 * rollup and subcategory walks that read them, and the file cache they are
 * stored in.
 */
class CategoryMapsTest extends zcUnitTestCase
{
    private static string $cacheDir = '';

    public function setUp(): void
    {
        if (self::$cacheDir === '') {
            self::$cacheDir = sys_get_temp_dir() . '/zc_catmaps_test_' . getmypid();
        }
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }

        if (!defined('DIR_FS_SQL_CACHE')) {
            define('DIR_FS_SQL_CACHE', self::$cacheDir);
        }
        if (!defined('DB_DATABASE')) {
            define('DB_DATABASE', 'zc_unit_test');
        }
        if (!defined('DB_PREFIX')) {
            define('DB_PREFIX', '');
        }
        if (!defined('PRODUCTS_MANUFACTURERS_STATUS')) {
            define('PRODUCTS_MANUFACTURERS_STATUS', '1');
        }
        /**
         * Zero keeps the on-disk half out of the map tests; the cache itself is
         * exercised directly further down with an explicit lifetime.
         */
        if (!defined('CATEGORY_MAPS_CACHE_SECONDS')) {
            define('CATEGORY_MAPS_CACHE_SECONDS', 0);
        }

        parent::setUp();

        // TABLE_* and DIR_FS_SQL_CACHE come from the core includes the parent loads
        if (!defined('TOPMOST_CATEGORY_PARENT_ID')) {
            define('TOPMOST_CATEGORY_PARENT_ID', 0);
        }

        require_once DIR_FS_CATALOG . 'includes/functions/functions_filecache.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_manufacturers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
    }

    public function tearDown(): void
    {
        foreach (glob(self::$cacheDir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        unset($GLOBALS['db']);

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$cacheDir !== '' && is_dir(self::$cacheDir)) {
            foreach (glob(self::$cacheDir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir(self::$cacheDir);
        }
    }

    /**
     * A three-level tree: 1 -> 2 -> 3, plus a childless sibling 4.
     */
    private function sampleMaps(): array
    {
        return [
            'children' => [
                0 => [1, 4],
                1 => [2],
                2 => [3],
            ],
            'counts' => [
                1 => ['all' => 10, 'active' => 7],
                2 => ['all' => 5, 'active' => 2],
                3 => ['all' => 1, 'active' => 1],
                4 => ['all' => 3, 'active' => 0],
            ],
        ];
    }

    public function testRollupAddsEveryDescendantCount(): void
    {
        $maps = $this->sampleMaps();

        $this->assertSame(16, zen_rollup_category_product_count(1, $maps, 'all'), '1 + its subtree');
        $this->assertSame(10, zen_rollup_category_product_count(1, $maps, 'active'));
        $this->assertSame(6, zen_rollup_category_product_count(2, $maps, 'all'));
        $this->assertSame(1, zen_rollup_category_product_count(3, $maps, 'all'), 'leaf');
        $this->assertSame(0, zen_rollup_category_product_count(4, $maps, 'active'), 'all disabled');
    }

    public function testRollupFromTheTopmostParentCoversTheWholeTree(): void
    {
        $maps = $this->sampleMaps();

        $this->assertSame(19, zen_rollup_category_product_count(0, $maps, 'all'));
        $this->assertSame(10, zen_rollup_category_product_count(0, $maps, 'active'));
    }

    public function testRollupReturnsZeroForAnUnknownCategory(): void
    {
        $this->assertSame(0, zen_rollup_category_product_count(999, $this->sampleMaps(), 'all'));
    }

    public function testRollupTerminatesOnACyclicParentChain(): void
    {
        $maps = [
            'children' => [1 => [2], 2 => [1]],
            'counts' => [1 => ['all' => 2, 'active' => 2], 2 => ['all' => 3, 'active' => 3]],
        ];

        $this->assertSame(5, zen_rollup_category_product_count(1, $maps, 'all'));
    }

    public function testCollectSubcategoriesWalksDepthFirstAndReturnsIntegers(): void
    {
        $found = [];
        zen_collect_subcategories($found, 0, $this->sampleMaps());

        $this->assertSame([1, 2, 3, 4], $found);
        /**
         * Every id must already be an integer; callers build SQL IN() lists from
         * these. Compared this way rather than with assertContainsOnly(), which
         * is deprecated for native types in PHPUnit 11+ but whose typed
         * replacements do not exist in the 9.x that the 2.3 branch pins.
         */
        $this->assertSame($found, array_map('intval', $found));
    }

    public function testCollectSubcategoriesLeavesTheArrayAloneForALeaf(): void
    {
        $found = [];
        zen_collect_subcategories($found, 3, $this->sampleMaps());

        $this->assertSame([], $found);
    }

    public function testCollectSubcategoriesTerminatesOnACyclicParentChain(): void
    {
        $found = [];
        zen_collect_subcategories($found, 1, ['children' => [1 => [2], 2 => [1]], 'counts' => []]);

        $this->assertSame([2, 1], $found);
    }

    /**
     * Regression: an outer join groups products that have no
     * products_to_categories row under a NULL category id, which casts to 0 in
     * PHP and silently inflates the topmost-category count that admin's
     * copy_product and move_product read.
     */
    public function testCountsQueryUsesAnInnerJoinSoUncategorisedProductsAreExcluded(): void
    {
        $db = new CategoryMapsDatabaseDouble(
            $this->resultWithRows([['categories_id' => '1', 'parent_id' => '0']]),
            $this->resultWithRows([['categories_id' => '1', 'total_all' => '4', 'total_active' => '3']])
        );
        $GLOBALS['db'] = $db;

        zen_get_category_maps(true);

        $countsQuery = $db->queries[1];
        $this->assertStringContainsStringIgnoringCase('INNER JOIN', $countsQuery);
        $this->assertStringNotContainsStringIgnoringCase('LEFT JOIN', $countsQuery);
    }

    public function testNullCategoryGroupNeverBecomesCategoryZero(): void
    {
        $db = new CategoryMapsDatabaseDouble(
            $this->resultWithRows([['categories_id' => '1', 'parent_id' => '0']]),
            $this->resultWithRows([
                ['categories_id' => null, 'total_all' => '9', 'total_active' => '9'],
                ['categories_id' => '1', 'total_all' => '4', 'total_active' => '3'],
            ])
        );
        $GLOBALS['db'] = $db;

        $maps = zen_get_category_maps(true);

        $this->assertArrayNotHasKey(0, $maps['counts']);
        $this->assertSame(['all' => 4, 'active' => 3], $maps['counts'][1]);
    }

    public function testAGenuineCategoryZeroGroupIsKept(): void
    {
        $db = new CategoryMapsDatabaseDouble(
            $this->resultWithRows([['categories_id' => '1', 'parent_id' => '0']]),
            $this->resultWithRows([
                ['categories_id' => null, 'total_all' => '9', 'total_active' => '9'],
                ['categories_id' => '0', 'total_all' => '2', 'total_active' => '1'],
            ])
        );
        $GLOBALS['db'] = $db;

        $maps = zen_get_category_maps(true);

        $this->assertSame(['all' => 2, 'active' => 1], $maps['counts'][0],
            'a NULL group must not overwrite a real category 0 group');
    }

    public function testChildrenMapIsKeyedByIntegerParentId(): void
    {
        $db = new CategoryMapsDatabaseDouble(
            $this->resultWithRows([
                ['categories_id' => '1', 'parent_id' => '0'],
                ['categories_id' => '2', 'parent_id' => '1'],
            ]),
            $this->resultWithRows([])
        );
        $GLOBALS['db'] = $db;

        $maps = zen_get_category_maps(true);

        $this->assertSame([0 => [1], 1 => [2]], $maps['children']);
    }

    public function testFileCacheRoundTripsIntegerKeyedData(): void
    {
        $payload = ['children' => [0 => [1, 2]], 'counts' => [7 => ['all' => 3, 'active' => 2]]];

        $this->assertTrue(zen_file_cache_write('unit_roundtrip', $payload));
        $this->assertSame($payload, zen_file_cache_read('unit_roundtrip', 3600));
    }

    public function testFileCacheMissesWhenTheEntryIsOlderThanItsLifetime(): void
    {
        zen_file_cache_write('unit_expiry', ['a' => 1]);
        touch(zen_file_cache_path('unit_expiry'), time() - 7200);
        clearstatcache(true, zen_file_cache_path('unit_expiry'));

        $this->assertNull(zen_file_cache_read('unit_expiry', 3600));
        $this->assertSame(['a' => 1], zen_file_cache_read('unit_expiry', 99999));
    }

    public function testFileCacheIsDisabledByAZeroLifetime(): void
    {
        zen_file_cache_write('unit_zero', ['a' => 1]);

        $this->assertNull(zen_file_cache_read('unit_zero', 0));
    }

    public function testFileCacheTreatsCorruptContentAsAMiss(): void
    {
        zen_file_cache_write('unit_corrupt', ['a' => 1]);
        file_put_contents(zen_file_cache_path('unit_corrupt'), '{"truncated":');

        $this->assertNull(zen_file_cache_read('unit_corrupt', 3600));
    }

    public function testFileCacheLeavesNoTemporaryFilesBehind(): void
    {
        for ($i = 0; $i < 5; $i++) {
            zen_file_cache_write('unit_tmp', ['n' => $i]);
        }

        $this->assertSame([], glob(self::$cacheDir . '/*.tmp') ?: []);
    }

    public function testClearingRemovesTheEntry(): void
    {
        zen_file_cache_write('catmaps', ['children' => [], 'counts' => []]);
        $this->assertFileExists(zen_file_cache_path('catmaps'));

        zen_clear_category_map_cache();

        $this->assertFileDoesNotExist(zen_file_cache_path('catmaps'));
    }

    public function testManufacturersBoxCacheKeyDistinguishesTheTwoVariants(): void
    {
        $this->assertSame('manufacturersbox_instock', zen_manufacturers_box_cache_key(true));
        $this->assertSame('manufacturersbox_all', zen_manufacturers_box_cache_key(false));
        $this->assertNotSame(
            zen_file_cache_path(zen_manufacturers_box_cache_key(true)),
            zen_file_cache_path(zen_manufacturers_box_cache_key(false))
        );
    }

    public function testClearingManufacturersCacheRemovesBothVariants(): void
    {
        zen_file_cache_write(zen_manufacturers_box_cache_key(true), [['manufacturers_id' => 1]]);
        zen_file_cache_write(zen_manufacturers_box_cache_key(false), [['manufacturers_id' => 2]]);

        zen_clear_manufacturers_box_cache();

        $this->assertFileDoesNotExist(zen_file_cache_path(zen_manufacturers_box_cache_key(true)));
        $this->assertFileDoesNotExist(zen_file_cache_path(zen_manufacturers_box_cache_key(false)));
    }

    private function resultWithRows(array $rows): \queryFactoryResult
    {
        $result = new \queryFactoryResult(null);
        $result->is_cached = true;
        $result->result = $rows;
        $result->EOF = ($rows === []);
        if ($rows !== []) {
            $result->fields = $rows[0];
        }

        return $result;
    }
}

/**
 * Records the SQL it is handed and returns canned result sets in order.
 */
class CategoryMapsDatabaseDouble
{
    public array $queries = [];
    private array $results;

    public function __construct(\queryFactoryResult ...$results)
    {
        $this->results = $results;
    }

    public function Execute(string $sql): \queryFactoryResult
    {
        $this->queries[] = $sql;

        return array_shift($this->results) ?? new \queryFactoryResult(null);
    }
}
