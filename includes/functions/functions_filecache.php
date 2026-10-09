<?php
/**
 * functions_filecache.php
 *
 * Small, dependency-free file cache for derived data that is expensive to
 * rebuild but identical for every visitor, such as the category structure maps
 * behind the categories sidebox.
 *
 * This is deliberately separate from the SQL cache in includes/classes/cache.php:
 *  - It does not look at SQL_CACHE_METHOD, which ships as 'none', so it works on
 *    a default install instead of silently doing nothing.
 *  - It caches an assembled PHP array, not one query's result set, so a single
 *    entry can stand in for hundreds of queries.
 *  - It writes via a temp file plus rename() so a reader can never see a
 *    half-written entry.
 *  - It stores JSON rather than serialize(), so a tampered cache file cannot be
 *    turned into a PHP object-injection vector.
 *
 * Every function here degrades silently. If the cache directory is missing or
 * read-only, reads return null and writes return false, and callers fall back to
 * building the data themselves.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */

/**
 * Absolute path of the cache file backing the given key.
 *
 * The database name and table prefix are folded into the hash so that two
 * installs that share one cache directory, or one database holding several
 * prefixed stores, cannot read each other's entries.
 *
 * @since ZC v2.3.0
 */
function zen_file_cache_path(string $key): string
{
    $scope = (defined('DB_DATABASE') ? DB_DATABASE : '') . '|' . (defined('DB_PREFIX') ? DB_PREFIX : '');

    /**
     * The readable prefix is only there to make the directory listing
     * intelligible; the hash is what actually keeps entries apart.
     */
    $label = preg_replace('/[^a-z0-9_]/', '', strtolower($key));

    return DIR_FS_SQL_CACHE . '/zc_' . $label . '_' . md5($scope . '|' . $key) . '.json';
}

/**
 * Read a cache entry, or return null when it is missing, stale or unreadable.
 *
 * @param int $ttl lifetime in seconds; 0 or less disables the cache entirely
 * @since ZC v2.3.0
 */
function zen_file_cache_read(string $key, int $ttl): ?array
{
    if ($ttl <= 0) {
        return null;
    }

    $file = zen_file_cache_path($key);

    /**
     * filemtime() is the freshness stamp, matching how the core SQL cache
     * decides expiry. Suppressed because a missing file is the normal cold case,
     * not an error worth logging on every request.
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
 * Write a cache entry. Returns false if the cache directory is not writable.
 *
 * @since ZC v2.3.0
 */
function zen_file_cache_write(string $key, array $data): bool
{
    $raw = json_encode($data);
    if ($raw === false) {
        return false;
    }

    $file = zen_file_cache_path($key);

    /**
     * uniqid() rather than getmypid(): shared hosts sometimes list getmypid()
     * in disable_functions, and the entropy only has to make concurrent writers
     * pick different temp names.
     */
    $tmp = $file . '.' . uniqid('', true) . '.tmp';

    if (@file_put_contents($tmp, $raw) === false) {
        return false;
    }

    /**
     * rename() is atomic within a filesystem, so concurrent readers see either
     * the old entry or the new one, never a partial write. Writing in place
     * (as the core SQL cache does) can hand a reader a truncated file.
     */
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }

    return true;
}

/**
 * Delete a cache entry. Safe to call when the entry does not exist.
 *
 * @since ZC v2.3.0
 */
function zen_file_cache_clear(string $key): void
{
    @unlink(zen_file_cache_path($key));
}
