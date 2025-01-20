<?php
/**
 * cache Class.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * cache Class.
 * handles query caching
 *
 */
class cache extends base {

  function sql_cache_exists($zf_query, $zf_cachetime=null) {
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
      $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
      $zp_cache_exists = $db->Execute($sql);
      if ($zp_cache_exists->RecordCount() > 0 && (is_null($zf_cachetime) || !$this->sql_cache_is_expired($zf_query, $zf_cachetime))) {
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

  function sql_cache_is_expired($zf_query, $zf_cachetime) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
      if (@filemtime(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql') > (time() - $zf_cachetime)) {
        return false;
      } else {
        return true;
      }
      break;
      case 'database':
      $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name ."'";
      $cache_result = $db->Execute($sql);
      if ($cache_result->RecordCount() > 0) {
        $start_time = $cache_result->fields['cache_entry_created'];
        if (time() - $start_time > $zf_cachetime) return true;
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

  function sql_cache_expire_now($zf_query) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
      if ($this->sql_cache_exists($zf_query)) {
          switch (SQL_CACHE_METHOD) {
              case 'file':
                  @unlink(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql');
                  return true;
                  break;
              case 'database':
                  $sql = "delete from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
                  $db->Execute($sql);
                  return true;
                  break;
              case 'memory':
                  unset($this->cache_array[$zp_cache_name]);
                  return true;
                  break;
              case 'none':
              default:
                  return true;
                  break;
          }
      }
  }

  function sql_cache_store($zf_query, $zf_result_array) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
      $OUTPUT = serialize($zf_result_array);
      $fp = fopen(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql',"w");
      fputs($fp, $OUTPUT);
      fclose($fp);
      return true;
      break;
      case 'database':
      $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
      $zp_cache_exists = $db->Execute($sql);
      if ($zp_cache_exists->RecordCount() > 0) {
        return true;
      }
      $result_serialize = $db->prepare_input(base64_encode(serialize($zf_result_array)));
      $sql = "insert ignore into " . TABLE_DB_CACHE . " (cache_entry_name, cache_data, cache_entry_created) VALUES (:cachename, :cachedata, unix_timestamp() )";
      $sql = $db->bindVars($sql, ':cachename', $zp_cache_name, 'string');
      $sql = $db->bindVars($sql, ':cachedata', $result_serialize, 'string');
      $db->Execute($sql);
      return true;
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

  function sql_cache_read($zf_query) {
    global $db;
    $zp_cache_name = $this->cache_generate_cache_name($zf_query);
    switch (SQL_CACHE_METHOD) {
      case 'file':
      $zp_fa = file(DIR_FS_SQL_CACHE . '/' . $zp_cache_name . '.sql');
      if ($zp_fa === false) return false;
      $zp_result_array = unserialize(implode('', $zp_fa));
      return $zp_result_array;
      break;
      case 'database':
      $sql = "select * from " . TABLE_DB_CACHE . " where cache_entry_name = '" . $zp_cache_name . "'";
      $zp_cache_result = $db->Execute($sql);
      $zp_result_array = unserialize(base64_decode($zp_cache_result->fields['cache_data']));
      return $zp_result_array;
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

  function sql_cache_flush_cache() {
    global $db;
    switch (SQL_CACHE_METHOD) {
      case 'file':
      if ($za_dir = @dir(DIR_FS_SQL_CACHE)) {
        while ($zv_file = $za_dir->read()) {
          if (strstr($zv_file, '.sql') && strstr($zv_file, 'zc_')) {
            @unlink(DIR_FS_SQL_CACHE . '/' . $zv_file);
          }
        }
        $za_dir->close();
      }
      return true;
      break;
      case 'database':
      $sql = "delete from " . TABLE_DB_CACHE;
      $db->Execute($sql);
      return true;
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

  function cache_generate_cache_name($zf_query) {
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
