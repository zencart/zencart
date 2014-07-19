<?php
/**
 * phpBB3 Class.
 *
 * This class is used to interact with phpBB3 forum
 *
 * @package classes
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: class.phpbb.php 14689 2009-10-26 17:06:43Z drbyte $
 */

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

  class phpBB extends base {
      var $debug = false;
      var $db_phpbb;
      var $phpBB=array();
      var $dir_phpbb='';
      var $groupId = 2;

    function phpBB() {
      $this->debug = (defined('PHPBB_DEBUG_MODE') && strtoupper(PHPBB_DEBUG_MODE)=='ON') ? (defined('PHPBB_DEBUG_IP') && (PHPBB_DEBUG_IP == '' || PHPBB_DEBUG_IP == $_SERVER['REMOTE_ADDR'] || strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) ? true : false ) : false;
      $this->phpBB = Array();
      $this->phpBB['installed'] = false;
      if (PHPBB_LINKS_ENABLED =='true') {  // if disabled in Zen Cart admin, don't do any checks for phpBB
        $this->get_phpBB_info();

        $this->db_phpbb = new queryFactory();
        $connect_status = $this->db_phpbb->connect($this->phpBB['dbhost'], $this->phpBB['dbuser'], $this->phpBB['dbpasswd'], $this->phpBB['dbname'], USE_PCONNECT, false);

        $this->check_connect();
        $this->set_status();
      } elseif ($this->debug==true) {
        echo "phpBB connection disabled in Admin<br>";
      }
      if ($this->debug==true) echo '<br /><br /><strong>YOU CAN IGNORE THE FOLLOWING "Cannot send session cache limited - headers already sent..." errors, as they are a result of the above debug output.</strong><br><br>';
    }

    function get_phpBB_info() {
      $this->phpBB['db_installed'] = false;
      $this->phpBB['files_installed'] = false;
      $this->phpBB['phpbb_path']='';
      $this->phpBB['phpbb_url']='';

      //@TODO: $cleaned = preg_replace('/\//',DIRECTORY_SEPARATOR,$string);

      $this->dir_phpbb = str_replace(array('\\', '//'), '/', DIR_WS_PHPBB ); // convert slashes

      if (substr($this->dir_phpbb,-1)!='/') $this->dir_phpbb .= '/'; // ensure has a trailing slash
      if ($this->debug==true) echo 'dir='.$this->dir_phpbb.'<br>';

      //check if file exists
      if (@file_exists($this->dir_phpbb . 'config.php')) {
        $this->phpBB['files_installed'] = true;
        if ($this->debug==true) echo "files_installed = true<br>";
        // if exists, also store it for future use
        $this->phpBB['phpbb_path'] = $this->dir_phpbb;
        if ($this->debug==true) echo 'phpbb_path='. $this->dir_phpbb . '<br><br>';

       // find phpbb table prefix without including file:
        $lines = array();
        $lines = @file($this->phpBB['phpbb_path']. 'config.php');
        foreach($lines as $line) { // read the configure.php file for specific variables
          if ($this->debug==true && strlen($line)>3 && substr($line,0,2)!='//' && !strstr($line,'$dbpasswd')) echo 'CONFIG.PHP-->'.$line.'<br>';
          if (substr($line,0,1)!='$') continue;
          if (substr_count($line,'"')>1) $delim='"';
          if (substr_count($line,"'")>1) $delim="'"; // determine whether single or double quotes used in this line.
          $def_string=array();
          $def_string=explode($delim,trim($line));
          if (substr($line,0,7)=='$dbhost') $this->phpBB['dbhost'] = ($def_string[1] == '' ? 'localhost' : $def_string[1]);
          if (substr($line,0,7)=='$dbname') $this->phpBB['dbname'] = $def_string[1];
          if (substr($line,0,7)=='$dbuser') $this->phpBB['dbuser'] = $def_string[1];
          if (substr($line,0,9)=='$dbpasswd') $this->phpBB['dbpasswd'] = $def_string[1];
          if (substr($line,0,13)=='$table_prefix') $this->phpBB['table_prefix'] = $def_string[1];
        }//end foreach $line
       // find phpbb table-names without INCLUDEing file:
        if (@file_exists($this->phpBB['phpbb_path'] . 'includes/constants.php')) {
          $lines = array();
          $lines = @file($this->phpBB['phpbb_path']. 'includes/constants.php');
          if (is_array($lines)) {
            foreach($lines as $line) { // read the configure.php file for specific variables
              if (substr_count($line,'define(')<1) continue;
              if ($this->debug==true && strlen($line)>3 && substr($line,0,1)!='/') echo 'CONSTANTS.PHP-->'.$line.'<br>';
              if (substr_count($line,'"')>1) $delim='"';
              if (substr_count($line,"'")>1) $delim="'"; // determine whether single or double quotes used in this line.
              $def_string=array();
              $def_string=explode($delim,$line);
              if ($def_string[1]=='USERS_TABLE')      $this->phpBB['users_table'] = $this->phpBB['table_prefix'] . $def_string[3];
              if ($def_string[1]=='USER_GROUP_TABLE') $this->phpBB['user_group_table'] = $this->phpBB['table_prefix'] . $def_string[3];
              if ($def_string[1]=='GROUPS_TABLE')     $this->phpBB['groups_table'] = $this->phpBB['table_prefix'] . $def_string[3];
              if ($def_string[1]=='CONFIG_TABLE')     $this->phpBB['config_table'] = $this->phpBB['table_prefix'] . $def_string[3];
            }//end foreach of $line
          }
        } else {
          $this->phpBB['files_installed'] = false;
        }
        if ($this->debug==true) {
          echo 'prefix='.$this->phpBB['table_prefix'].'<br>';
          echo 'dbname='.$this->phpBB['dbname'].'<br>';
          echo 'dbuser='.$this->phpBB['dbuser'].'<br>';
          echo 'dbhost='.$this->phpBB['dbhost'].'<br>';
          echo 'dbpasswd='.$this->phpBB['dbpasswd'].'<br>';
          echo 'users_table='.$this->phpBB['users_table'].'<br>';
          echo 'user_group_table='.$this->phpBB['user_group_table'].'<br>';
          echo 'groups_table='.$this->phpBB['groups_table'].'<br>';
          echo 'config_table='.$this->phpBB['config_table'].'<br>';
        }

      }//endif @file_exists
    }

    function check_connect() {
        // check if tables exist in database
        if ($this->phpBB['dbname']!='' && $this->phpBB['dbuser'] !='' && $this->phpBB['dbhost'] !='' && $this->phpBB['config_table']!='' && $this->phpBB['users_table'] !='' && $this->phpBB['user_group_table'] !='' && $this->phpBB['groups_table']!='') {
          if ($this->phpBB['dbname'] == DB_DATABASE) {
            $this->phpBB['db_installed'] = $this->table_exists_zen($this->phpBB['users_table']);
            $this->phpBB['db_installed_config'] = $this->table_exists_zen($this->phpBB['config_table']);
            if ($this->debug==true) echo "db_installed -- in ZC Database = ".$this->phpBB['db_installed']."<br>";
            } else {
            $this->phpBB['db_installed'] = $this->table_exists_phpbb($this->phpBB['users_table']);
            $this->phpBB['db_installed_config'] = $this->table_exists_phpbb($this->phpBB['config_table']);
            if ($this->debug==true) echo "db_installed -- in separate database = ".$this->phpBB['db_installed']."<br>";
          }
        }
    }

    function set_status() {
      //calculate the path from root of server for absolute path info
      $script_filename = $_SERVER['PATH_TRANSLATED'];
      if (empty($script_filename)) $script_filename = $_SERVER['SCRIPT_FILENAME'];
      $script_filename = str_replace(array('\\', '//'), '/', $script_filename);  //convert slashes

      if ($this->debug==true) echo "script-filename=".$script_filename.'<br>';
      if ($this->debug==true) echo "link_enabled_admin_status=".PHPBB_LINKS_ENABLED.'<br>';

      if ( ($this->phpBB['db_installed']) && ($this->phpBB['files_installed'])  && (PHPBB_LINKS_ENABLED=='true')) {
       //good so far. now let's check for relative path access so we can successfully "include" the config.php file when needed.
        if ($this->debug==true) echo "ok, now let's check relative paths<br>";
        if ($this->debug==true) echo 'docroot='.$_SERVER['DOCUMENT_ROOT'].'<br>';
        if ($this->debug==true) echo 'phpself='.$_SERVER['PHP_SELF'].'<br>';
        $this->phpBB['phpbb_url'] = str_replace(array($_SERVER['DOCUMENT_ROOT'],substr($script_filename,0,strpos($script_filename,$_SERVER['PHP_SELF']))),'',$this->phpBB['phpbb_path']);
        $this->phpBB['installed'] = true;
        if ($this->debug==true) echo 'URL='.$this->phpBB['phpbb_url'].'<br>';
        //if neither of the relative paths validate, the function still returns false for 'installed'.
      }
      if ($this->debug==true && $this->phpBB['installed']==false) echo "FAILURE: phpBB NOT activated<br><br>";
     // will use $phpBB->phpBB['installed'] to check for suitability of calling phpBB in the future.
    }


    function table_exists_zen($table_name) {
      global $db;
    // Check to see if the requested Zen Cart table exists
      $sql = "SHOW TABLES like '".$table_name."'";
      $tables = $db->Execute($sql);
//echo 'tables_found = '. $tables->RecordCount() .'<br>';
      if ($tables->RecordCount() > 0) {
        $found_table = true;
      }
      return $found_table;
    }
    function table_exists_phpbb($table_name) {
    // Check to see if the requested PHPBB table exists, regardless of which database it's set to use
      $sql = "SHOW TABLES like '".$table_name."'";
      $tables = $this->db_phpbb->Execute($sql);
      //echo 'tables_found = '. $tables->RecordCount() .'<br>';
      if ($tables->RecordCount() > 0) {
        $found_table = true;
      }
      return $found_table;
    }

    function phpbb_create_account($nick, $password, $email_address) {
      if ($this->phpBB['installed'] != true || !zen_not_null($password) || !zen_not_null($email_address) || !zen_not_null($nick)) return false;
      if (!$this->phpbb_check_for_duplicate_email($email_address) == 'already_exists') {
        $sql = "select max(user_id) as total from " . $this->phpBB['users_table'];
        $phpbb_users = $this->db_phpbb->Execute($sql);
        $user_id = ($phpbb_users->fields['total'] + 1);
        $sql = "insert into " . $this->phpBB['users_table'] . "
                (user_id, group_id, username, username_clean, user_password, user_email, user_email_hash, user_regdate, user_permissions, user_sig, user_occ, user_interests)
                values
                ('" . (int)$user_id . "', " . $this->groupId . ", '" . $nick . "', '" . strtolower($nick) . "', '" . md5($password) . "', '" . $email_address . "', '" . crc32(strtolower($email_address)) . strlen($email_address) . "', '" . time() ."', '', '', '', '')";
        $this->db_phpbb->Execute($sql);
        $sql = " update " . $this->phpBB['config_table'] . " SET config_value = '{$user_id}' WHERE config_name = 'newest_user_id'";
        $this->db_phpbb->Execute($sql);
        $sql = " update " . $this->phpBB['config_table'] . " SET config_value = '{$nick}' WHERE config_name = 'newest_username'";
        $this->db_phpbb->Execute($sql);
        $sql = " update " . $this->phpBB['config_table'] . " SET config_value = config_value + 1 WHERE config_name = 'num_users'";
        $this->db_phpbb->Execute($sql);
        $sql = "INSERT INTO " . $this->phpBB['user_group_table'] . " (user_id, group_id, user_pending)
                VALUES ($user_id, $this->groupId, 0)";
        $this->db_phpbb->Execute($sql);
      }
    }
    function v2phpbb_create_account($nick, $password, $email_address) {
      if ($this->phpBB['installed'] != true || !zen_not_null($password) || !zen_not_null($email_address) || !zen_not_null($nick)) return false;
      if ($this->phpbb_check_for_duplicate_email($email_address) == 'already_exists') {
//        $this->phpbb_change_email($old_email, $email_address);
      } else {
        $sql = "select max(user_id) as total from " . $this->phpBB['users_table'];
        $phpbb_users = $this->db_phpbb->Execute($sql);
        $user_id = ($phpbb_users->fields['total'] + 1);
        $sql = "insert into " . $this->phpBB['users_table'] . "
                (user_id, username, user_password, user_email, user_regdate)
                values
                ('" . (int)$user_id . "', '" . $nick . "', '" . md5($password) . "', '" . $email_address . "', '" . time() ."')";
        $this->db_phpbb->Execute($sql);

        $sql = "INSERT INTO " . $this->phpBB['groups_table'] . " (group_name, group_description, group_single_user, group_moderator)
                VALUES (0, 'Personal User', 1, 0)";
        $this->db_phpbb->Execute($sql);
        $group_id = $this->db_phpbb->Insert_ID();
        $sql = "INSERT INTO " . $this->phpBB['user_group_table'] . " (user_id, group_id, user_pending)
                VALUES ($user_id, $group_id, 0)";
        $this->db_phpbb->Execute($sql);
        //might optionally send an extra email welcoming them to the phpBB forum, reminding them of their nickname?
      }
    }

    function phpbb_check_for_duplicate_nick($nick='') {
      if ($this->phpBB['installed'] != true || empty($nick)) return false;
      $status='';
      $sql = "select * from " . $this->phpBB['users_table'] . " where username = '" . $nick . "'";
      //echo $sql;
      $phpbb_users = $this->db_phpbb->Execute($sql);
      //echo "count=".$phpbb_users->RecordCount();
      if ($phpbb_users->RecordCount() > 0 ) {
        $status='already_exists';
      }
      return $status;
    }

    function phpbb_check_for_duplicate_email($email_address) {
      if ($this->phpBB['installed'] != true) return false;
      $status='';
      $sql = "select * from " . $this->phpBB['users_table'] . " where user_email = '" . $email_address . "'";
      $phpbb_users = $this->db_phpbb->Execute($sql);
      if ($phpbb_users->RecordCount() > 0 ) {
        $status='already_exists';
      }
      return $status;
    }

    function phpbb_change_password($nick, $newpassword) {
      if ($this->phpBB['installed'] != true || !zen_not_null($nick) || $nick == '') return false;
        $sql = "update " . $this->phpBB['users_table'] . " set user_password='" . MD5($newpassword) . "'
                where username = '" . $nick . "'";
        $phpbb_users = $this->db_phpbb->Execute($sql);
    }

    function phpbb_change_email($old_email, $email_address) {
    // before utilizing this function, we should do an MD5 password validation first
      if ($this->phpBB['installed'] != true || !zen_not_null($email_address) || $email_address == '') return false;
        $sql = "update " . $this->phpBB['users_table'] . " set user_email='" . $email_address . "', user_email_hash = '" . crc32(strtolower($email_address)) . strlen($email_address) . "'
                where user_email = '" . $old_email . "'";
        $phpbb_users = $this->db_phpbb->Execute($sql);
    }

    function phpbb_change_nick($old_nick, $new_nick) {
    // before utilizing this function, we should do an MD5 password validation first
      if ($this->phpBB['installed'] != true || !zen_not_null($nick) || $nick == '') return false;
        $sql = "update " . $this->phpBB['users_table'] . " set username='" . $new_nick . "', username_clean = '" . $new_nick . "'
                where username = '" . $old_nick . "'";
        $phpbb_users = $this->db_phpbb->Execute($sql);
    }

  }
