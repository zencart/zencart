<?php
/**
 * cache Class.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * cache Class.
 * handles query caching
 *
 * @since ZC v1.2.0d
 */
class cache
{
    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_exists($zf_query, $zf_cachetime = null): bool
    {
        global $db;
        $zp_cache_name = $this->cache_generate_cache_name($zf_query);
        switch (SQL_CACHE_METHOD) {
            case 'file':
                // where using a single directory at the moment. Need to look at splitting into subdirectories
                // like adodb
                if (file_exists(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql') && (is_null($zf_cachetime) || !$this->sql_cache_is_expired($zf_query, $zf_cachetime))) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'database':
                $sql = "SELECT * FROM " . TABLE_DB_CACHE . " WHERE cache_entry_name = '" . $zp_cache_name . "'";
                $zp_cache_exists = $db->Execute($sql);
                if (!$zp_cache_exists->EOF && (is_null($zf_cachetime) || !$this->sql_cache_is_expired($zf_query, $zf_cachetime))) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'memory':
                return false;
                break;
            case 'none':
            default:
                return false;
                break;
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_is_expired($zf_query, $zf_cachetime): bool
    {
        global $db;
        $zp_cache_name = $this->cache_generate_cache_name($zf_query);
        switch (SQL_CACHE_METHOD) {
            case 'file':
                $filename = DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql';
                if (file_exists($filename) && @filemtime($filename) > (time() - $zf_cachetime)) {
                    return false;
                } else {
                    return true;
                }
                break;
            case 'database':
                $sql = "SELECT * FROM " . TABLE_DB_CACHE . " WHERE cache_entry_name = '" . $zp_cache_name ."'";
                $cache_result = $db->Execute($sql);
                if (!$cache_result->EOF) {
                    $start_time = $cache_result->fields['cache_entry_created'];
                    if (time() - $start_time > $zf_cachetime) {
                        return true;
                    }
                    return false;
                } else {
                    return true;
                }
                break;
            case 'memory':
                return true;
                break;
            case 'none':
            default:
                return true;
                break;
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_expire_now($zf_query): void
    {
        global $db;
        $zp_cache_name = $this->cache_generate_cache_name($zf_query);
        if ($this->sql_cache_exists($zf_query)) {
            switch (SQL_CACHE_METHOD) {
                case 'file':
                    $filename = DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql';
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                    break;
                case 'database':
                    $sql = "DELETE FROM " . TABLE_DB_CACHE . " WHERE cache_entry_name = '" . $zp_cache_name . "'";
                    $db->Execute($sql);
                    break;
                case 'memory':
                case 'none':
                default:
                    break;
            }
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_store($zf_query, $zf_result_array): void
    {
        global $db;
        $zp_cache_name = $this->cache_generate_cache_name($zf_query);
        switch (SQL_CACHE_METHOD) {
            case 'file':
                file_put_contents(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql', serialize($zf_result_array));
                break;
            case 'database':
                $sql = "SELECT * FROM " . TABLE_DB_CACHE . " WHERE cache_entry_name = '" . $zp_cache_name . "'";
                $zp_cache_exists = $db->Execute($sql);
                if (!$zp_cache_exists->EOF) {
                    break;
                }
                $result_serialize = $db->prepare_input(base64_encode(serialize($zf_result_array)));
                $sql = "INSERT IGNORE INTO " . TABLE_DB_CACHE . " (cache_entry_name, cache_data, cache_entry_created) VALUES (:cachename, :cachedata, unix_timestamp() )";
                $sql = $db->bindVars($sql, ':cachename', $zp_cache_name, 'string');
                $sql = $db->bindVars($sql, ':cachedata', $result_serialize, 'string');
                $db->Execute($sql);
                break;
            case 'memory':
            case 'none':
            default:
                break;
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_read($zf_query): bool|array
    {
        global $db;
        $zp_cache_name = $this->cache_generate_cache_name($zf_query);
        switch (SQL_CACHE_METHOD) {
            case 'file':
                $zp_fa = file(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql');
                if ($zp_fa === false) {
                    return false;
                }
                $zp_result_array = unserialize(implode('', $zp_fa));
                return $zp_result_array;
                break;
            case 'database':
                $sql = "SELECT * FROM " . TABLE_DB_CACHE . " WHERE cache_entry_name = '" . $zp_cache_name . "'";
                $zp_cache_result = $db->Execute($sql);
                if ($zp_cache_result->EOF) {
                    return false;
                }
                $zp_result_array = unserialize(base64_decode($zp_cache_result->fields['cache_data']));
                return $zp_result_array;
                break;
            case 'memory':
            case 'none':
            default:
                return true;
                break;
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function sql_cache_flush_cache(): void
    {
        global $db;
        switch (SQL_CACHE_METHOD) {
            case 'file':
                if ($za_dir = @dir(DIR_FS_SQL_CACHE)) {
                    while ($zv_file = $za_dir->read()) {
                        if (str_ends_with($zv_file, '.sql') && str_starts_with($zv_file, 'zc_')) {
                            unlink(DIR_FS_SQL_CACHE . '/' . $zv_file);
                        }
                    }
                    $za_dir->close();
                }
                break;
            case 'database':
                $sql = "DELETE FROM " . TABLE_DB_CACHE;
                $db->Execute($sql);
                break;
            case 'memory':
            case 'none':
            default:
                break;
        }
    }

    /**
     * @since ZC v1.2.0d
     */
    public function cache_generate_cache_name($zf_query): bool|string
    {
        switch (SQL_CACHE_METHOD) {
            case 'file':
                return 'zc_' . hash('md5', $zf_query);
                break;
            case 'database':
                return 'zc_' . hash('md5', $zf_query);
                break;
            case 'memory':
                return 'zc_' . hash('md5', $zf_query);
                break;
            case 'none':
            default:
                return true;
                break;
        }
    }
}
