<?php
declare(strict_types=1);

/**
 * FileCache class.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */

namespace Zencart\Cache;

/**
 * A small file cache for derived data that is expensive to rebuild but identical
 * for every visitor -- the assembled category maps behind the categories
 * sidebox, the manufacturers list, and other navigation lists of that shape.
 *
 * This is deliberately a separate thing from the two existing caches:
 *
 *  - `cache` (includes/classes/cache.php) caches one query's result set and does
 *    nothing at all unless SQL_CACHE_METHOD is set away from its shipped value
 *    of 'none'. That is why the sidebox work could not use it: the lifetimes
 *    already present in the code were inert on a default store.
 *  - `QueryCache` memoizes identical SELECTs within a single request only.
 *
 * This one caches an assembled PHP array rather than a result set, so a single
 * entry can stand in for hundreds of queries; it ignores SQL_CACHE_METHOD, so it
 * works on a default install; it stores JSON rather than serialize(), so a
 * tampered cache file cannot become a PHP object-injection vector; and it writes
 * through a temp file plus rename(), so a reader can never see a half-written
 * entry.
 *
 * Every method degrades silently. If the cache directory is missing or not
 * writable, reads return null and writes return false, and the caller is
 * expected to fall back to building the data itself.
 *
 * Instantiate it where you need it -- there is no shared global. Construction is
 * two string operations, and avoiding a global keeps it usable from places where
 * globals are unreliable, such as a shutdown function.
 *
 *     $cache = new \Zencart\Cache\FileCache();
 *     $brands = $cache->read('brandsbox', 3600);
 *     if ($brands === null) {
 *         $brands = ... // build it
 *         $cache->write('brandsbox', $brands);
 *     }
 *
 * @since ZC v2.3.0
 */
class FileCache
{
    protected string $directory;

    /**
     * Folded into every cache file name so that two installs sharing one cache
     * directory, or one database holding several prefixed stores, cannot read
     * each other's entries.
     */
    protected string $scope;

    /**
     * @param string|null $directory defaults to DIR_FS_SQL_CACHE
     * @param string|null $scope defaults to the database name and table prefix
     * @since ZC v2.3.0
     */
    public function __construct(?string $directory = null, ?string $scope = null)
    {
        if ($directory === null) {
            $directory = defined('DIR_FS_SQL_CACHE') ? DIR_FS_SQL_CACHE : '';
        }
        $this->directory = rtrim($directory, '/');

        if ($scope === null) {
            $scope = (defined('DB_DATABASE') ? DB_DATABASE : '') . '|' . (defined('DB_PREFIX') ? DB_PREFIX : '');
        }
        $this->scope = $scope;
    }

    /**
     * Absolute path of the file backing the given key.
     *
     * The readable prefix exists only to make a directory listing intelligible;
     * the hash is what actually keeps entries apart.
     *
     * @since ZC v2.3.0
     */
    public function path(string $key): string
    {
        $label = preg_replace('/[^a-z0-9_]/', '', strtolower($key));

        return $this->directory . '/zc_' . $label . '_' . md5($this->scope . '|' . $key) . '.json';
    }

    /**
     * Read an entry, or return null when it is missing, stale or unreadable.
     *
     * @param int $ttl lifetime in seconds; 0 or less disables the cache entirely
     * @since ZC v2.3.0
     */
    public function read(string $key, int $ttl): ?array
    {
        if ($ttl <= 0) {
            return null;
        }

        $file = $this->path($key);

        /**
         * filemtime() is the freshness stamp, matching how the core SQL cache
         * decides expiry. Suppressed because a missing file is the normal cold
         * case, not an error worth logging on every request.
         */
        $mtime = @filemtime($file);
        if ($mtime === false || $mtime <= time() - $ttl) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Read a cached pool and return up to $count of its entries, chosen at
     * random, as a list.
     *
     * This is for the sideboxes that are deliberately random -- specials,
     * featured, new products and the like. Those cannot cache their *output*,
     * because the point is that it differs per page view, but they can cache a
     * larger pool of candidates once and pick from it per request, which turns
     * a query on every page view into a query once per lifetime.
     *
     * Two things are the caller's responsibility. The pool has to be enough
     * larger than $count that the variety still reads as random -- if a box
     * shows 3 of a pool of 4, visitors will notice. And the pool is frozen for
     * the lifetime, so something newly added to the catalog will not appear in
     * the box until the entry expires; a shorter lifetime trades queries for
     * freshness.
     *
     * Returns null on a miss, exactly like read(), so the caller knows to
     * rebuild. Returns the whole pool (shuffled) when it holds $count or fewer.
     *
     * shuffle() rather than zen_rand(): this chooses what to display, not
     * anything security-sensitive, and it has to be cheap enough to run on every
     * page view.
     *
     * @since ZC v2.3.0
     */
    public function readRandom(string $key, int $ttl, int $count): ?array
    {
        $pool = $this->read($key, $ttl);
        if ($pool === null) {
            return null;
        }

        if ($count < 1 || $pool === []) {
            return [];
        }

        $keys = array_keys($pool);
        shuffle($keys);
        $keys = array_slice($keys, 0, $count);

        $picked = [];
        foreach ($keys as $poolKey) {
            $picked[] = $pool[$poolKey];
        }

        return $picked;
    }

    /**
     * Write an entry. Returns false when the directory is not writable.
     *
     * @since ZC v2.3.0
     */
    public function write(string $key, array $data): bool
    {
        $raw = json_encode($data);
        if ($raw === false) {
            return false;
        }

        $file = $this->path($key);

        /**
         * uniqid() rather than getmypid(): shared hosts sometimes list
         * getmypid() in disable_functions, and the entropy only has to make
         * concurrent writers pick different temp names.
         */
        $tmp = $file . '.' . uniqid('', true) . '.tmp';

        if (@file_put_contents($tmp, $raw) === false) {
            return false;
        }

        /**
         * rename() is atomic within a filesystem, so concurrent readers see
         * either the old entry or the new one, never a partial write. Writing in
         * place, as the core SQL cache does, can hand a reader a truncated file.
         */
        if (!@rename($tmp, $file)) {
            @unlink($tmp);
            return false;
        }

        return true;
    }

    /**
     * Delete an entry. Safe to call when it does not exist.
     *
     * @since ZC v2.3.0
     */
    public function clear(string $key): void
    {
        @unlink($this->path($key));
    }
}
