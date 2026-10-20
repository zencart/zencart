<?php

declare(strict_types=1);

namespace Tests\Unit\testsCategories;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\Support\ScriptedDatabase;
use Tests\Support\zcUnitTestCase;

/**
 * Unit tests for the legacy categories-sidebox tree builder.
 *
 * The tree is built from scripted queries. These tests use this small catalogue:
 *
 *   1 Hardware -> 11 Drives -> 111 Internal, 12 Cables
 *   2 Software
 *   3 Books
 */
#[AllowMockObjectsWithoutExpectations]
#[RunTestsInSeparateProcesses]
class CategoryTreeTest extends zcUnitTestCase
{
    use ScriptedDatabase;

    public function setUp(): void
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . 'includes/classes/class.base.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/classes/category_tree.php';

        defined('TOPMOST_CATEGORY_PARENT_ID') || define('TOPMOST_CATEGORY_PARENT_ID', '0');
        define('CATEGORIES_SUBCATEGORIES_INDENT', '--');
        define('CATEGORIES_SEPARATOR_SUBS', '>');
        define('SHOW_COUNTS', 'false');

        $_SESSION['languages_id'] = 1;
        $GLOBALS['cPath'] = '';
        $GLOBALS['cPath_array'] = [];

        $this->useScriptedDatabase();
        $this->respondTo('/SELECT type_master_type FROM/', [['type_master_type' => 7]]);

        $this->respondTo('/c\.parent_id = 0\b/', [
            $this->categoryRow(1, 'Hardware', 0, 'hardware.png'),
            $this->categoryRow(2, 'Software', 0, 'software.png'),
            $this->categoryRow(3, 'Books', 0, ''),
        ]);
        $this->respondTo('/c\.parent_id = 1\b/', [
            $this->categoryRow(11, 'Drives', 1, 'drives.png'),
            $this->categoryRow(12, 'Cables', 1, ''),
        ]);
        $this->respondTo('/c\.parent_id = 11\b/', [
            $this->categoryRow(111, 'Internal', 11, ''),
        ]);

        $this->respondTo('/count\(\*\) as count\s+FROM \S+\s+WHERE parent_id = 1\b/', [['count' => 2]]);
        $this->respondTo('/count\(\*\) as count\s+FROM \S+\s+WHERE parent_id = 11\b/', [['count' => 1]]);
        $this->respondTo('/count\(\*\) as count/', [['count' => 0]]);
    }

    public function testTopLevelCategoriesAreListedInOrderWithTheirDetails(): void
    {
        $box = (new \category_tree())->zen_category_tree();

        $this->assertSame(
            [
                0 => ['top' => 'true', 'path' => 'cPath=1', 'current' => false, 'name' => 'Hardware', 'image' => 'hardware.png', 'has_sub_cat' => true],
                1 => ['top' => 'true', 'path' => 'cPath=2', 'current' => false, 'name' => 'Software', 'image' => 'software.png', 'has_sub_cat' => false],
                2 => ['top' => 'true', 'path' => 'cPath=3', 'current' => false, 'name' => 'Books', 'image' => '', 'has_sub_cat' => false],
            ],
            $box
        );
    }

    public function testProductTypeZeroAndAllBuildTheSameUnfilteredTree(): void
    {
        $default = (new \category_tree())->zen_category_tree();
        $all = (new \category_tree())->zen_category_tree('all');
        $zero = (new \category_tree())->zen_category_tree(0);

        $this->assertSame($default, $all);
        $this->assertSame($default, $zero);
        $this->assertSame([], $this->sqlMatching('/type_master_type/'), 'no product-type lookup for the unfiltered tree');
        $topQueries = $this->sqlMatching('/c\.parent_id = 0\b/');
        $this->assertStringContainsString('c.categories_status= 1', $topQueries[0]);
        $this->assertStringContainsString("cd.language_id='1'", $topQueries[0]);
        $this->assertStringNotContainsString('product_type_id', $topQueries[0]);
        $this->assertStringContainsString('ORDER BY sort_order, cd.categories_name', $topQueries[0]);
    }

    public function testProductTypeFiltersByTheMasterTypeOfTheRequestedType(): void
    {
        $this->respondToTypedTree();

        (new \category_tree())->zen_category_tree(3);

        $typeQuery = $this->sqlMatching('/type_master_type/');
        $this->assertCount(1, $typeQuery);
        $this->assertStringContainsString('WHERE type_master_type = 3', $typeQuery[0]);
        $treeQuery = $this->sqlMatching('/c\.parent_id = 0\b/');
        $this->assertStringContainsString('ptc.product_type_id = 7', $treeQuery[0]);
        $this->assertStringContainsString('ptc.category_id as categories_id', $treeQuery[0]);
        $this->assertStringContainsString('cd.language_id=1', $treeQuery[0]);
    }

    public function testSelectedCategoryExpandsItsChildrenInPlace(): void
    {
        $GLOBALS['cPath'] = '1';
        $GLOBALS['cPath_array'] = [1];

        $box = (new \category_tree())->zen_category_tree();

        $this->assertSame(
            ['Hardware', '-->Drives', '-->Cables', 'Software', 'Books'],
            array_column($box, 'name')
        );
        $this->assertSame(['cPath=1', 'cPath=1_11', 'cPath=1_12', 'cPath=2', 'cPath=3'], array_column($box, 'path'));
        $this->assertSame(['true', 'false', 'false', 'true', 'true'], array_column($box, 'top'));
        $this->assertSame([true, false, false, false, false], array_column($box, 'current'));
        $this->assertSame([true, true, false, false, false], array_column($box, 'has_sub_cat'));
        $this->assertSame(['hardware.png', 'drives.png', '', 'software.png', ''], array_column($box, 'image'));
    }

    public function testNestedSelectionExpandsEachLevelOfThePath(): void
    {
        $GLOBALS['cPath'] = '1_11';
        $GLOBALS['cPath_array'] = [1, 11];

        $box = (new \category_tree())->zen_category_tree();

        $this->assertSame(
            ['Hardware', '-->Drives', '---->Internal', '-->Cables', 'Software', 'Books'],
            array_column($box, 'name')
        );
        $this->assertSame(
            ['cPath=1', 'cPath=1_11', 'cPath=1_11_111', 'cPath=1_12', 'cPath=2', 'cPath=3'],
            array_column($box, 'path')
        );
        $this->assertSame([true, true, false, false, false, false], array_column($box, 'current'));
        $this->assertSame(['true', 'false', 'false', 'false', 'true', 'true'], array_column($box, 'top'));
    }

    public function testSelectionOfACategoryWithoutChildrenStopsExpanding(): void
    {
        $GLOBALS['cPath'] = '2_99';
        $GLOBALS['cPath_array'] = [2, 99];

        $box = (new \category_tree())->zen_category_tree();

        $this->assertSame(['Hardware', 'Software', 'Books'], array_column($box, 'name'));
        $this->assertSame([false, true, false], array_column($box, 'current'));
        $this->assertCount(1, $this->sqlMatching('/c\.parent_id = 2\b/'));
        $this->assertSame([], $this->sqlMatching('/c\.parent_id = 99\b/'), 'expansion stops at the first level with no children');
    }

    public function testTypedTreeAlsoFiltersTheExpandedChildren(): void
    {
        $this->respondToTypedTree();
        $GLOBALS['cPath'] = '1';
        $GLOBALS['cPath_array'] = [1];

        $box = (new \category_tree())->zen_category_tree(3);

        $this->assertSame(['Hardware', '-->Drives', '-->Cables', 'Software', 'Books'], array_column($box, 'name'));
        $childQuery = $this->sqlMatching('/c\.parent_id = 1\b/');
        $this->assertCount(1, $childQuery);
        $this->assertStringContainsString('ptc.product_type_id = 7', $childQuery[0]);
        $this->assertStringContainsString('cd.language_id=1', $childQuery[0]);
        $this->assertStringContainsString('c.categories_id = ptc.category_id', $childQuery[0]);
    }

    public function testUnfilteredQueriesUseTheExpectedSql(): void
    {
        $GLOBALS['cPath'] = '1';
        $GLOBALS['cPath_array'] = [1];

        (new \category_tree())->zen_category_tree();

        $this->assertSame(
            [
                "SELECT c.categories_id, cd.categories_name, c.parent_id, c.categories_image FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd"
                . " WHERE c.parent_id = 0 AND c.categories_id = cd.categories_id AND cd.language_id='1' AND c.categories_status= 1"
                . " ORDER BY sort_order, cd.categories_name",
                "SELECT c.categories_id, cd.categories_name, c.parent_id, c.categories_image FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd"
                . " WHERE c.parent_id = 1 AND c.categories_id = cd.categories_id AND cd.language_id=1 AND c.categories_status= 1"
                . " ORDER BY sort_order, cd.categories_name",
            ],
            $this->treeQueries()
        );
        $this->assertSame(['', true, 150], $this->cacheArguments('/c\.parent_id = 0\b/'));
    }

    public function testProductTypeQueriesUseTheExpectedSql(): void
    {
        $this->respondToTypedTree();
        $GLOBALS['cPath'] = '1';
        $GLOBALS['cPath_array'] = [1];

        (new \category_tree())->zen_category_tree(3);

        $from = " FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc";
        $select = "SELECT ptc.category_id as categories_id, cd.categories_name, c.parent_id, c.categories_image";
        $this->assertSame(
            [
                "SELECT type_master_type FROM " . TABLE_PRODUCT_TYPES . " WHERE type_master_type = 3",
            ],
            $this->normalizedSql('/type_master_type/')
        );
        $this->assertSame(
            [
                $select . $from . " WHERE c.parent_id = 0 AND ptc.category_id = cd.categories_id AND ptc.product_type_id = 7"
                . " AND c.categories_id = ptc.category_id AND cd.language_id=1 AND c.categories_status= 1"
                . " ORDER BY sort_order, cd.categories_name",
                $select . $from . " WHERE c.parent_id = 1 AND ptc.category_id = cd.categories_id AND ptc.product_type_id = 7"
                . " AND c.categories_id = ptc.category_id AND cd.language_id=1 AND c.categories_status= 1"
                . " ORDER BY sort_order, cd.categories_name",
            ],
            $this->treeQueries()
        );
        $this->assertSame(['', true, 150], $this->cacheArguments('/c\.parent_id = 0\b/'));
    }

    public function testRequestValuesAreCastToIntegersInTheQueries(): void
    {
        $this->queryResponses = [];
        $this->respondTo('/SELECT type_master_type FROM/', [['type_master_type' => '7 OR 1']]);
        $this->respondTo('/c\.parent_id = 0\b/', [$this->categoryRow(1, 'Hardware', 0, '')]);
        $this->respondTo('/c\.parent_id = 1\b/', [$this->categoryRow(11, 'Drives', 1, '')]);
        $this->respondTo('/count\(\*\) as count/', [['count' => 0]]);
        $_SESSION['languages_id'] = '2abc';
        $GLOBALS['cPath'] = '1abc';
        $GLOBALS['cPath_array'] = ['1abc'];

        (new \category_tree())->zen_category_tree('3abc');

        $this->assertSame([], $this->sqlMatching('/abc| OR /'));
        $this->assertStringContainsString('type_master_type = 3', $this->executedQueries[0]['sql']);
        foreach ($this->treeQueries() as $sql) {
            $this->assertStringContainsString('ptc.product_type_id = 7 ', $sql);
            $this->assertStringContainsString('cd.language_id=2 ', $sql);
        }
        $this->assertCount(2, $this->treeQueries());
    }

    public function testRequestValuesAreCastToIntegersInTheUnfilteredQueries(): void
    {
        $_SESSION['languages_id'] = '2abc';
        $GLOBALS['cPath'] = '1abc';
        $GLOBALS['cPath_array'] = ['1abc'];

        (new \category_tree())->zen_category_tree();

        $this->assertSame([], $this->sqlMatching('/abc/'));
        $queries = $this->treeQueries();
        $this->assertCount(2, $queries);
        $this->assertStringContainsString("cd.language_id='2' ", $queries[0]);
        $this->assertStringContainsString('c.parent_id = 1 ', $queries[1]);
        $this->assertStringContainsString('cd.language_id=2 ', $queries[1]);
    }

    public function testSingleCategoriesCanBeRenderedAfterTheTreeIsBuilt(): void
    {
        $tree = new \category_tree();
        $tree->zen_category_tree();

        $box = $tree->zen_show_category(2, 7);

        $this->assertSame('Software', $box[7]['name']);
        $this->assertSame('cPath=2', $box[7]['path']);
        $this->assertSame('true', $box[7]['top']);
        $this->assertSame('Books', $box[8]['name'], 'rendering continues with the next category in the list');
    }

    public function testProductCountsAreIncludedWhenEnabledAndIncludeSubcategories(): void
    {
        $GLOBALS['cPath'] = '';
        $this->respondToCounts();

        $box = $this->buildTreeWithCounts();

        $this->assertSame([6, 0, 3], array_column($box, 'count'));
    }

    public function testNoCountKeyWhenCountsAreDisabled(): void
    {
        $box = (new \category_tree())->zen_category_tree();

        $this->assertArrayNotHasKey('count', $box[0]);
    }

    /**
     * setUp() defines SHOW_COUNTS as 'false' and constants cannot be redefined. zen_config() reads
     * a configuration repository first when one exists, so a fake repository supplies 'true' here.
     */
    private function buildTreeWithCounts(): array
    {
        $GLOBALS['configurationRepository'] = new class {
            public function get(string $key): mixed
            {
                return match ($key) {
                    'SHOW_COUNTS' => 'true',
                    'CATEGORIES_SUBCATEGORIES_INDENT' => '--',
                    'CATEGORIES_SEPARATOR_SUBS' => '>',
                    default => null,
                };
            }
        };
        $GLOBALS['productTypeLayoutRepository'] = new class {
            public function get(string $key): mixed
            {
                return null;
            }
        };

        return (new \category_tree())->zen_category_tree();
    }

    private function respondToCounts(): void
    {
        // Specific categories first: the first matching pattern wins.
        $this->queryResponses = array_merge(
            [
                ['pattern' => '/count\(\*\) as total.*p2c\.categories_id = 1\b/s', 'rows' => [['total' => 4]]],
                ['pattern' => '/count\(\*\) as total.*p2c\.categories_id = 11\b/s', 'rows' => [['total' => 2]]],
                ['pattern' => '/count\(\*\) as total.*p2c\.categories_id = 3\b/s', 'rows' => [['total' => 3]]],
                ['pattern' => '/SELECT categories_id\s+FROM \S+\s+WHERE parent_id = 1\b/', 'rows' => [['categories_id' => 11]]],
                ['pattern' => '/count\(\*\) as total/', 'rows' => [['total' => 0]]],
                ['pattern' => '/SELECT categories_id\s+FROM \S+\s+WHERE parent_id = \d+/', 'rows' => []],
            ],
            $this->queryResponses
        );
    }

    private function respondToTypedTree(): void
    {
        array_unshift($this->queryResponses, [
            'pattern' => '/ptc\.product_type_id = 7.*c\.parent_id = 0\b|c\.parent_id = 0\b.*ptc\.product_type_id = 7/s',
            'rows' => [
                $this->categoryRow(1, 'Hardware', 0, 'hardware.png'),
                $this->categoryRow(2, 'Software', 0, 'software.png'),
                $this->categoryRow(3, 'Books', 0, ''),
            ],
        ]);
        array_unshift($this->queryResponses, [
            'pattern' => '/ptc\.product_type_id = 7.*c\.parent_id = 1\b|c\.parent_id = 1\b.*ptc\.product_type_id = 7/s',
            'rows' => [
                $this->categoryRow(11, 'Drives', 1, 'drives.png'),
                $this->categoryRow(12, 'Cables', 1, ''),
            ],
        ]);
    }

    private function categoryRow(int $id, string $name, int $parentId, string $image): array
    {
        return ['categories_id' => $id, 'categories_name' => $name, 'parent_id' => $parentId, 'categories_image' => $image];
    }

    /**
     * @return string[] whitespace-normalised category-tree queries, in the order they were issued
     */
    private function treeQueries(): array
    {
        return $this->normalizedSql('/c\.parent_id = \d+/');
    }

    /**
     * @return string[]
     */
    private function normalizedSql(string $pattern): array
    {
        return array_map(
            static fn(string $sql): string => trim((string)preg_replace('/\s+/', ' ', $sql)),
            $this->sqlMatching($pattern)
        );
    }

    /**
     * @return array{0: mixed, 1: mixed, 2: mixed} the limit, cache flag and cache lifetime of the first matching query
     */
    private function cacheArguments(string $pattern): array
    {
        foreach ($this->executedQueries as $query) {
            if (preg_match($pattern, $query['sql']) === 1) {
                return [$query['limit'], $query['cache'], $query['ttl']];
            }
        }

        return [];
    }
}
