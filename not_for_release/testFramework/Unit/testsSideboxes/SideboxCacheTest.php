<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSideboxes;

use Tests\Support\zcUnitTestCase;
use Zencart\Cache\FileCache;

/**
 * Covers prefix clearing, and that zen_clear_sidebox_caches() really does drop
 * every sidebox dataset -- the thing that breaks silently if a new cached box is
 * added without being registered there.
 */
class SideboxCacheTest extends zcUnitTestCase
{
    private static string $cacheDir = '';

    public function setUp(): void
    {
        if (self::$cacheDir === '') {
            self::$cacheDir = sys_get_temp_dir() . '/zc_sidebox_test_' . getmypid();
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

        parent::setUp();

        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'Cache/FileCache.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_manufacturers.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_categories.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_sideboxes.php';
    }

    public function tearDown(): void
    {
        foreach (glob(self::$cacheDir . '/*') ?: [] as $file) {
            @unlink($file);
        }
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

    public function testClearPrefixRemovesOnlyTheMatchingEntries(): void
    {
        $cache = new FileCache();
        $cache->write('ezpagesbox_1', [['pages_id' => 1]]);
        $cache->write('ezpagesbox_2', [['pages_id' => 2]]);
        $cache->write('musicgenresbox', [['music_genre_id' => 1]]);

        $removed = $cache->clearPrefix('ezpagesbox');

        $this->assertSame(2, $removed);
        $this->assertNull($cache->read('ezpagesbox_1', 3600));
        $this->assertNull($cache->read('ezpagesbox_2', 3600));
        $this->assertNotNull($cache->read('musicgenresbox', 3600), 'an unrelated entry must survive');
    }

    public function testClearPrefixIsSafeWhenNothingMatches(): void
    {
        $this->assertSame(0, (new FileCache())->clearPrefix('nosuchboxatall'));
    }

    public function testClearPrefixRefusesAnEmptyPrefixRatherThanWipingEverything(): void
    {
        $cache = new FileCache();
        $cache->write('musicgenresbox', [['music_genre_id' => 1]]);

        $this->assertSame(0, $cache->clearPrefix(''));
        $this->assertSame(0, $cache->clearPrefix('///'), 'a prefix that sanitizes to nothing is also refused');
        $this->assertNotNull($cache->read('musicgenresbox', 3600));
    }

    /**
     * The registry has to list every cached sidebox. A new one added without
     * being registered would go stale after an admin edit, which is the kind of
     * bug that only shows up as wrong numbers on the storefront.
     */
    public function testClearingSideboxCachesDropsEveryKnownDataset(): void
    {
        $cache = new FileCache();

        $keys = [
            'brandsbox_instock',
            'brandsbox_all',
            'musicgenresbox',
            'recordcompaniesbox',
            'documentcategoriesguard',
            'ezpagesbox_1',
            'ezpagesbox_3',
            'catmaps',
            zen_manufacturers_box_cache_key(true),
            zen_manufacturers_box_cache_key(false),
        ];
        foreach ($keys as $key) {
            $cache->write($key, ['planted' => $key]);
        }
        foreach ($keys as $key) {
            $this->assertNotNull($cache->read($key, 3600), "failed to plant $key");
        }

        zen_clear_sidebox_caches();

        foreach ($keys as $key) {
            $this->assertNull($cache->read($key, 3600), "$key was left behind by zen_clear_sidebox_caches()");
        }
    }

    public function testClearingSideboxCachesLeavesUnrelatedEntriesAlone(): void
    {
        $cache = new FileCache();
        $cache->write('someplugindata', ['keep' => true]);

        zen_clear_sidebox_caches();

        $this->assertSame(['keep' => true], $cache->read('someplugindata', 3600),
            'clearing sidebox caches must not touch other callers of the cache');
    }
}
