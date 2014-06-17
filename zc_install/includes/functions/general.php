<?php
/**
 * general functions used by the installer
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Wed Oct 23 18:28:44 2013 +0100 Modified in v1.5.2 $
 */

  if (!defined('TABLE_UPGRADE_EXCEPTIONS')) define('TABLE_UPGRADE_EXCEPTIONS','upgrade_exceptions');

  function zen_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string, ENT_COMPAT, CHARSET, TRUE);
    } else {
      if ($translate == false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
  }

////
  function zen_db_input($string) {
    return addslashes($string);
  }

////
  function zen_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

function setInputValue($input, $constant, $default) {
  if (isset($input)) {
    define($constant, $input);
  } else {
    define($constant, $default);
  }
}

function setRadioChecked($input, $constant, $default) {
  if ($input == '') {
  $input = $default;
  }
  if ($input == 'true') {
  define($constant . '_FALSE', '');
  define($constant . '_TRUE', 'checked="checked" ');
  } else {
  define($constant . '_FALSE', 'checked="checked" ');
  define($constant . '_TRUE', '');
  }
}

function setSelected($input, $selected) {
  if ($input == $selected) {
    return ' selected="selected"';
  }
}
function executeSql($sql_file, $database, $table_prefix = '', $isupgrade=false) {
  $debug=false;
  if (!defined('DB_PREFIX')) define('DB_PREFIX', $table_prefix);
//	  echo 'start SQL execute';
  global $db;

  $ignored_count=0;
  $ignore_line=false;
  $results=0;
  $string='';
  $result='';
  $collateSuffix = '';
  $errors=array();

  // prepare for upgrader processing
  if ($isupgrade) zen_create_upgrader_table(); // only creates table if doesn't already exist

  if (version_compare(PHP_VERSION, 5.4, '>=') || !get_cfg_var('safe_mode')) {
    @set_time_limit(1200);
  }

  $counter = 0;
  $lines = file($sql_file);
  $newline = '';
  $lines_to_keep_together_counter=0;
//  $saveline = '';
  foreach ($lines as $line) {
    $line = trim($line);
//    $line = $saveline . $line;
    $keep_together = 1; // count of number of lines to treat as a single command

     // split the line into words ... starts at $param[0] and so on.  Also remove the ';' from end of last param if exists
     $param=explode(" ",(substr($line,-1)==';') ? substr($line,0,strlen($line)-1) : $line);
     if (!isset($param[4])) $param[4] = '';
     if (!isset($param[5])) $param[5] = '';

      // The following command checks to see if we're asking for a block of commands to be run at once.
      // Syntax: #NEXT_X_ROWS_AS_ONE_COMMAND:6     for running the next 6 commands together (commands denoted by a ;)
      if (substr($line,0,28) == '#NEXT_X_ROWS_AS_ONE_COMMAND:') $keep_together = substr($line,28);
      if (substr($line,0,1) != '#' && substr($line,0,1) != '-' && $line != '') {
//        if ($table_prefix != -1) {
//echo '*}'.$line.'<br>';

          $line_upper=strtoupper($line);
          switch (true) {
          case (substr($line_upper, 0, 21) == 'DROP TABLE IF EXISTS '):
            $line = 'DROP TABLE IF EXISTS ' . $table_prefix . substr($line, 21);
            break;
          case (substr($line_upper, 0, 11) == 'DROP TABLE ' && $param[2] != 'IF'):
            if (!$checkprivs = zen_check_database_privs('DROP')) $result=sprintf(REASON_NO_PRIVILEGES,'DROP');
            if (!zen_table_exists($param[2]) || zen_not_null($result)) {
              zen_write_to_upgrade_exceptions_table($line, (zen_not_null($result) ? $result : sprintf(REASON_TABLE_DOESNT_EXIST,$param[2])), $sql_file);
              $ignore_line=true;
              $result=(zen_not_null($result) ? $result : sprintf(REASON_TABLE_DOESNT_EXIST,$param[2])); //duplicated here for on-screen error-reporting
              break;
            } else {
              $line = 'DROP TABLE ' . $table_prefix . substr($line, 11);
            }
            break;
          case (substr($line_upper, 0, 13) == 'CREATE TABLE '):
            // check to see if table exists
            $table = (strtoupper($param[2].' '.$param[3].' '.$param[4]) == 'IF NOT EXISTS') ? $param[5] : $param[2];
            $result=zen_table_exists($table);
            if ($result==true) {
              $ignore_line=true;
              if (strtoupper($param[2].' '.$param[3].' '.$param[4]) != 'IF NOT EXISTS') {
                zen_write_to_upgrade_exceptions_table($line, sprintf(REASON_TABLE_ALREADY_EXISTS,$table), $sql_file);
                $result=sprintf(REASON_TABLE_ALREADY_EXISTS,$table); //duplicated here for on-screen error-reporting
              }
              break;
            } else {
              $line = (strtoupper($param[2].' '.$param[3].' '.$param[4]) == 'IF NOT EXISTS') ? 'CREATE TABLE IF NOT EXISTS ' . $table_prefix . substr($line, 27) : 'CREATE TABLE ' . $table_prefix . substr($line, 13);
              $collateSuffix = (strtoupper($param[3]) == 'AS' || (isset($param[6]) && strtoupper($param[6]) == 'AS')) ? '' : ' COLLATE ' . DB_CHARSET . '_general_ci';
            }
            break;
          case (substr($line_upper, 0, 13) == 'REPLACE INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[2])) $result=sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!';
            // check to see if INSERT command may be safely executed for "configuration" or "product_type_layout" tables
            if (($param[2]=='configuration'       && ($result=zen_check_config_key($line))) or
                ($param[2]=='product_type_layout' && ($result=zen_check_product_type_layout_key($line))) or
                ($param[2]=='configuration_group' && ($result=zen_check_cfggroup_key($line))) or
                (!$tbl_exists)    ) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'REPLACE INTO ' . $table_prefix . substr($line, 13);
            }
            break;
          case (substr($line_upper, 0, 12) == 'INSERT INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[2])) $result=sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!';
            // check to see if INSERT command may be safely executed for "configuration" or "product_type_layout" tables
            if (($param[2]=='configuration'       && ($result=zen_check_config_key($line))) or
                ($param[2]=='product_type_layout' && ($result=zen_check_product_type_layout_key($line))) or
                ($param[2]=='configuration_group' && ($result=zen_check_cfggroup_key($line))) or
                (!$tbl_exists)    ) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'INSERT INTO ' . $table_prefix . substr($line, 12);
            }
            break;
          case (substr($line_upper, 0, 19) == 'INSERT IGNORE INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[3])) {
              $result=sprintf(REASON_TABLE_NOT_FOUND,$param[3]).' CHECK PREFIXES!';
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'INSERT IGNORE INTO ' . $table_prefix . substr($line, 19);
            }
            break;
            case (substr($line_upper, 0, 19) == 'ALTER IGNORE TABLE '):
            // check to see if ALTER IGNORE command may be safely executed
            if ($result=zen_check_alter_command($param)) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'ALTER IGNORE TABLE ' . $table_prefix . substr($line, 19);
            }
            break;
            case (substr($line_upper, 0, 12) == 'ALTER TABLE '):
            //if (ZC_UPG_DEBUG3==true) echo 'ALTER -- Table check ('.$param[2].')' .'<br>';
            // check to see if ALTER command may be safely executed
            if ($result=zen_check_alter_command($param)) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'ALTER TABLE ' . $table_prefix . substr($line, 12);
            }
            break;
          case (substr($line_upper, 0, 15) == 'TRUNCATE TABLE '):
            // check to see if TRUNCATE command may be safely executed
            if (!$tbl_exists = zen_table_exists($param[2])) {
              $result=sprintf(REASON_TABLE_NOT_FOUND,$param[3]).' CHECK PREFIXES!';
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'TRUNCATE TABLE ' . $table_prefix . substr($line, 15);
            }
            break;
          case (substr($line_upper, 0, 13) == 'RENAME TABLE '):
            // RENAME TABLE command cannot be parsed unless it is split into two lines
            if (isset($param[3]) && $param[3] != '') {
              zen_write_to_upgrade_exceptions_table($line, 'RENAME TABLE command must be split onto 2 rows for proper parsing.  Or use phpMyAdmin instead.', $sql_file);
              $result=sprintf('RENAME TABLE [%s] command must be split onto 2 rows for proper parsing.',$param[2]).' CHECK PREFIXES!';
              $ignore_line=true;
            }
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[2])) {
              zen_write_to_upgrade_exceptions_table($line, sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!', $sql_file);
              $result=sprintf('RENAME TABLE problem: ' . REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!';
              $ignore_line=true;
              break;
            } else {
              $line = 'RENAME TABLE ' . $table_prefix . substr($line, 13);
            }
            break;
          case (substr($line_upper, 0, 3) == 'TO '):
            if (!isset($param[1]) || $param[1] == '') {
              zen_write_to_upgrade_exceptions_table($line, 'RENAME TABLE command must be split onto 2 rows (with TO clause on 2nd line) for proper parsing.  Or use phpMyAdmin instead.', $sql_file);
              $result=sprintf('RENAME TABLE problem: %s' ,$param[1]).' CHECK PREFIXES!';
              $ignore_line=true;
            } else {
              $line = 'TO ' . $table_prefix . substr($line, 3);
            }
            break;
          case (substr($line_upper, 0, 7) == 'UPDATE '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[1])) {
              zen_write_to_upgrade_exceptions_table($line, sprintf(REASON_TABLE_NOT_FOUND,$param[1]).' CHECK PREFIXES!', $sql_file);
              $result=sprintf(REASON_TABLE_NOT_FOUND,$param[1]).' CHECK PREFIXES!';
              $ignore_line=true;
              break;
            } else {
              $line = 'UPDATE ' . $table_prefix . substr($line, 7);
            }
            break;
          case (substr($line_upper, 0, 14) == 'UPDATE IGNORE '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = zen_table_exists($param[2])) {
              zen_write_to_upgrade_exceptions_table($line, sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!', $sql_file);
              $result=sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!';
              $ignore_line=true;
              break;
            } else {
              $line = 'UPDATE IGNORE ' . $table_prefix . substr($line, 14);
            }
            break;
          case (substr($line_upper, 0, 12) == 'DELETE FROM '):
            $line = 'DELETE FROM ' . $table_prefix . substr($line, 12);
            break;
          case (substr($line_upper, 0, 11) == 'DROP INDEX '):
            // check to see if DROP INDEX command may be safely executed
            if ($result=zen_drop_index_command($param)) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'DROP INDEX ' . $param[2] . ' ON ' . $table_prefix . $param[4];
            }
            break;
          case (substr($line_upper, 0, 13) == 'CREATE INDEX ' || (strtoupper($param[0])=='CREATE' && strtoupper($param[2])=='INDEX')):
            // check to see if CREATE INDEX command may be safely executed
            if ($result=zen_create_index_command($param)) {
              zen_write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              if (strtoupper($param[1])=='INDEX') {
                $line = trim('CREATE INDEX ' . $param[2] .' ON '. $table_prefix . implode(' ',array($param[4],$param[5],$param[6],$param[7],$param[8],$param[9],$param[10],$param[11],$param[12],$param[13])) ).';'; // add the ';' back since it was removed from $param at start
              } else {
                $line = trim('CREATE '. $param[1] .' INDEX ' .$param[3]. ' ON '. $table_prefix . implode(' ',array($param[5],$param[6],$param[7],$param[8],$param[9],$param[10],$param[11],$param[12],$param[13])) ); // add the ';' back since it was removed from $param at start
              }
            }
            break;
          case (substr($line_upper, 0, 7) == 'SELECT ' && substr_count($line,'FROM ')>0):
            $line = str_replace('FROM ','FROM '. $table_prefix, $line);
            break;
          case (substr($line_upper, 0, 10) == 'LEFT JOIN '):
            $line = 'LEFT JOIN ' . $table_prefix . substr($line, 10);
            break;
          case (substr($line_upper, 0, 5) == 'FROM '):
            if (substr_count($line,',')>0) { // contains FROM and a comma, thus must parse for multiple tablenames
              $tbl_list = explode(',',substr($line,5));
              $line = 'FROM ';
              foreach($tbl_list as $val) {
                $line .= $table_prefix . trim($val) . ','; // add prefix and comma
              } //end foreach
              if (substr($line,-1)==',') $line = substr($line,0,(strlen($line)-1)); // remove trailing ','
            } else { //didn't have a comma, but starts with "FROM ", so insert table prefix
              $line = str_replace('FROM ', 'FROM '.$table_prefix, $line);
            }//endif substr_count(,)
            break;
          default:
            break;
          } //end switch
//        } // endif $table_prefix
        $newline .= $line . ' ';

        if ( substr($line,-1) ==  ';') {
          //found a semicolon, so treat it as a full command, incrementing counter of rows to process at once
          if (substr($newline,-1)==' ') $newline = substr($newline,0,(strlen($newline)-1));
          $lines_to_keep_together_counter++;
          if ($lines_to_keep_together_counter == $keep_together) { // if all grouped rows have been loaded, go to execute.
            $complete_line = true;
            $lines_to_keep_together_counter=0;
            if ($collateSuffix != '' && @mysqli_get_server_info() >= '4.1' && (!defined('IGNORE_DB_CHARSET') || (defined('IGNORE_DB_CHARSET') && IGNORE_DB_CHARSET != FALSE))) {
              $newline = rtrim($newline, ';') . $collateSuffix . ';';
              $collateSuffix = '';
            }
          } else {
            $complete_line = false;
          }
        } //endif found ';'

        if ($complete_line) {
          if ($debug==true) echo ((!$ignore_line) ? '<br /><strong>About to execute.</strong>': '<strong>Ignoring statement. This command WILL NOT be executed.</strong>').'<br />Debug info:<br />$ line='.$line.'<br />$ complete_line='.$complete_line.'<br>$ keep_together='.$keep_together.'<br />SQL='.$newline.'<br /><br />';
          if (get_magic_quotes_runtime() > 0) $newline=stripslashes($newline);
          $output = (trim(str_replace(';','',$newline)) != '' && !$ignore_line) ? $db->Execute($newline) : '';
          $results++;
          $string .= $newline.'<br />';
          $return_output[]=$output;
          if (zen_not_null($result) && !zen_check_exceptions($result, $line) ) $errors[]=$result;
          // reset var's
          $newline = '';
          $keep_together=1;
          $complete_line = false;
          if ($ignore_line && !zen_check_exceptions($result, $line)) $ignored_count++;
          $ignore_line=false;

          // show progress bar
          global $zc_show_progress;
          if ($zc_show_progress=='yes') {
             $counter++;
             if (($counter/5) == (int)($counter/5)) echo '~ ';
             if ($counter>200) {
               echo '<br /><br />';
               $counter=0;
             }
             if (function_exists('ob_flush')) @ob_flush();
             @flush();
          }

        } //endif $complete_line

      } //endif ! # or -
    } // end foreach $lines
  return array('queries'=> $results, 'string'=>$string, 'output'=>$return_output, 'ignored'=>($ignored_count), 'errors'=>$errors);
  } //end function

  function zen_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(zen_sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = zen_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  function zen_sanitize_string($string) {
    $string = preg_replace('/ +/', ' ', $string);
    return preg_replace("/[<>]/", '_', $string);
  }

  function zen_validate_email($email = "root@localhost.localdomain") {
    $valid_address = true;
    $user ="";
    $domain="";
// split the e-mail address into user and domain parts
// need to update to trap for addresses in the format of "first@last"@someplace.com
// this method will most likely break in that case
  list( $user, $domain ) = explode( "@", $email );
  $valid_ip_form = '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
  $valid_email_pattern = '^[a-z0-9]+[a-z0-9_\.\'\-]*@[a-z0-9]+[a-z0-9\.\-]*\.(([a-z]{2,6})|([0-9]{1,3}))$';
  $space_check = '[ ]';

// strip beginning and ending quotes, if and only if both present
  if( (preg_match('/^["]/', $user) && preg_match('/["]$/', $user)) ){
    $user = preg_replace ( '/^["]/', '', $user );
    $user = preg_replace ( '/["]$/', '', $user );
    $user = preg_replace ( '/'.$space_check.'/', '', $user ); //spaces in quoted addresses OK per RFC (?)
    $email = $user."@".$domain; // contine with stripped quotes for remainder
  }

// if e-mail domain part is an IP address, check each part for a value under 256
  if (preg_match('/'.$valid_ip_form.'/', $domain)) {
    $digit = explode( ".", $domain );
    for($i=0; $i<4; $i++) {
    if ($digit[$i] > 255) {
      $valid_address = false;
      return $valid_address;
      exit;
    }
// stop crafty people from using internal IP addresses
    if (($digit[0] == 192) || ($digit[0] == 10)) {
      $valid_address = false;
      return $valid_address;
      exit;
    }
    }
  }

  if (!preg_match('/'.$space_check.'/', $email)) { // trap for spaces in
    if ( preg_match('/'.$valid_email_pattern.'/i', $email)) { // validate against valid e-mail patterns
    $valid_address = true;
    } else {
    $valid_address = false;
    return $valid_address;
    exit;
      }
    }

// Verify e-mail has an associated MX and/or A record.
// Need alternate method to deal with Verisign shenanigans and with Windows Servers
//		if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
//		  $valid_address = false;
//		}

    return $valid_address;
  }

  function zen_encrypt_password($plain) {
    $password = '';

    for ($i=0; $i<10; $i++) {
      $password .= zen_rand();
    }

    $salt = substr(md5($password), 0, 2);

    $password = md5($salt . $plain) . ':' . $salt;

    return $password;
  }

  function zen_validate_password($plain, $encrypted) {
    if (zen_not_null($plain) && zen_not_null($encrypted)) {
      $stack = explode(':', $encrypted);
      if (sizeof($stack) != 2) return false;
      if (md5($stack[1] . $plain) == $stack[0]) {
        return true;
      }
    }
    return false;
  }


  function zen_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  function zen_read_config_value($value, $onlyMainFile = TRUE, $concatenate = FALSE) {
    $files_array = array();
    $retVal = $string='';
    if (!$onlyMainFile) $files_array[] = '../includes/local/configure.php';
    $files_array[] = '../includes/configure.php';

//    if (!$onlyMainFile && $za_dir = @dir('../includes/' . 'extra_configures')) {
//      while ($zv_file = $za_dir->read()) {
//        if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
//          //echo $zv_file.'<br>';
//          $files_array[] = $zv_file;
//        }
//      }
//      $za_dir->close(); unset($za_dir);
//    }

    foreach ($files_array as $filename) {
      if (!file_exists($filename)) continue;
      //echo $filename . '!<br>';
      $lines = file($filename);
      foreach($lines as $line) { // read the configure.php file for specific variables
        if (substr(trim($line),0,2) == '//') continue;
        $def_string=array();
        $def_string=explode("'",$line);
        //define('CONSTANT','value');
        //[1]=TABLE_CONSTANT
        //[2]=,
        //[3]=value
        //[4]=);
        //[5]=
        if (isset($def_string[1]) && strtoupper($def_string[1]) == $value ) {
          $string = $def_string[3];
          continue;
        }
      } //end foreach $line
      if ($retVal == '' || ($concatenate == TRUE && $string != '')) {
        $retVal .= $string;
      }
    } //end foreach $filename
   return $retVal;
  }

  function zen_table_exists($tablename, $pre_install=false) {
    global $db, $db_test;
    if ($pre_install==true) {
      $tables = $db_test->Execute("SHOW TABLES like '" . DB_PREFIX . $tablename . "'");
    } else {
      $tables = $db->Execute("SHOW TABLES like '" . DB_PREFIX . $tablename . "'");
    }
    if (ZC_UPG_DEBUG3==true) echo 'Table check ('.$tablename.') = '. $tables->RecordCount() .'<br>';
    if ($tables->RecordCount() > 0) {
      return true;
    } else {
      return false;
    }
  }

  function zen_check_database_privs($priv='',$table='',$show_privs=false) {
    //bypass for now ... will attempt to use with modifications in a new release later
    if ($show_privs==true) return 'Not Checked|||Not Checked';
    return true;
    // end bypass
    global $zdb_server, $zdb_user, $zdb_name;
    if (!zen_not_null($zdb_server)) $zdb_server = zen_read_config_value('DB_SERVER', FALSE);
    if (!zen_not_null($zdb_user)) $zdb_user     = zen_read_config_value('DB_SERVER_USERNAME', FALSE);
    if (!zen_not_null($zdb_name)) $zdb_name     = zen_read_config_value('DB_DATABASE', FALSE);
    if (isset($_GET['nogrants']) || isset($_POST['nogrants']) ) return true; // bypass if flag set
    //Display permissions, or check for suitable permissions to carry out a particular task
      //possible outputs:
      //GRANT ALL PRIVILEGES ON *.* TO 'xyz'@'localhost' WITH GRANT OPTION
      //GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, FILE, INDEX, ALTER ON *.* TO 'xyz'@'localhost' IDENTIFIED BY PASSWORD '2344'
      //GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON `db1`.* TO 'xyz'@'localhost'
      //GRANT SELECT (id) ON db1.tablename TO 'xyz'@'localhost
    global $db;
    global $db_test;
    $granted_privs_list='';
    if (ZC_UPG_DEBUG3==true) echo '<br />Checking for priv: ['.(zen_not_null($priv) ? $priv : 'none specified').']<br />';
    if (!defined('DB_SERVER'))          define('DB_SERVER',$zdb_server);
    if (!defined('DB_SERVER_USERNAME')) define('DB_SERVER_USERNAME',$zdb_user);
    if (!defined('DB_DATABASE'))        define('DB_DATABASE',$zdb_name);
    $user = DB_SERVER_USERNAME."@".DB_SERVER;
    if ($user == 'DB_SERVER_USERNAME@DB_SERVER' || DB_DATABASE=='DB_DATABASE') return true; // bypass if constants not set properly
    $sql = "show grants for ".$user;
    if (ZC_UPG_DEBUG3==true) echo $sql.'<br />';
    if (is_object($db)) {
      $result = $db->Execute($sql);
    } elseif (is_object($db_test)) {
      $result = $db_test->Execute($sql);
    }
    while (!$result->EOF) {
      if (ZC_UPG_DEBUG3==true) echo $result->fields['Grants for '.$user].'<br />';
      $grant_syntax = $result->fields['Grants for '.$user] . ' ';
      $granted_privs = str_replace('GRANT ','',$grant_syntax); // remove "GRANT" keyword
      $granted_privs = substr($granted_privs,0,strpos($granted_privs,' TO ')); //remove anything after the "TO" keyword
      $granted_db = str_replace(array('`','\\'),'',substr($granted_privs,strpos($granted_privs,' ON ')+4) ); //remove backquote and find "ON" string
      if (ZC_UPG_DEBUG3==true) echo 'privs_list = '.$granted_privs.'<br />';
      if (ZC_UPG_DEBUG3==true) echo 'granted_db = '.$granted_db.'<br />';
      $db_priv_ok += ($granted_db == '*.*' || $granted_db==DB_DATABASE.'.*' || $granted_db==DB_DATABASE.'.'.$table) ? true : false;
      if (ZC_UPG_DEBUG3==true) echo 'db-priv-ok='.$db_priv_ok.'<br />';

      if ($db_priv_ok) {  // if the privs list pertains to the current database, or is *.*, carry on
        $granted_privs = substr($granted_privs,0,strpos($granted_privs,' ON ')); //remove anything after the "ON" keyword
        $granted_privs_list .= ($granted_privs_list=='') ? $granted_privs : ', '.$granted_privs;

        $specific_priv_found = (zen_not_null($priv) && substr_count($granted_privs,$priv)==1);
        if (ZC_UPG_DEBUG3==true) echo 'specific priv['.$priv.'] found ='.$specific_priv_found.'<br />';

        if (ZC_UPG_DEBUG3==true) echo 'spec+db='.($specific_priv_found && $db_priv_ok == true).' ||| ';
        if (ZC_UPG_DEBUG3==true) echo 'all+db='.($granted_privs == 'ALL PRIVILEGES' && $db_priv_ok==true).'<br /><br />';

        if (($specific_priv_found && $db_priv_ok == true) || ($granted_privs == 'ALL PRIVILEGES' && $db_priv_ok==true)) {
          return true; // privs found
        }
      } // endif $db_priv_ok
      $result->MoveNext();
    }
    if ($show_privs) {
      if (ZC_UPG_DEBUG3==true) echo 'LIST OF PRIVS='.$granted_privs_list.'<br />';
      return $db_priv_ok . '|||'. $granted_privs_list;
    } else {
    return false; // if not found, return false
    }
  }

  function zen_drop_index_command($param) {
    if (!$checkprivs = zen_check_database_privs('INDEX')) return sprintf(REASON_NO_PRIVILEGES,'INDEX');
    //this is only slightly different from the ALTER TABLE DROP INDEX command
    global $db;
    if (!zen_not_null($param)) return "Empty SQL Statement";
    $index = $param[2];
    $sql = "show index from " . DB_PREFIX . $param[4];
    $result = $db->Execute($sql);
    while (!$result->EOF) {
      if (ZC_UPG_DEBUG3==true) echo $result->fields['Key_name'].'<br />';
      if  ($result->fields['Key_name'] == $index) {
//        if (!$checkprivs = zen_check_database_privs('INDEX')) return sprintf(REASON_NO_PRIVILEGES,'INDEX');
        return; // if we get here, the index exists, and we have index privileges, so return with no error
      }
      $result->MoveNext();
    }
    // if we get here, then the index didn't exist
    return sprintf(REASON_INDEX_DOESNT_EXIST_TO_DROP,$index,$param[4]);
  }

  function zen_create_index_command($param) {
    //this is only slightly different from the ALTER TABLE CREATE INDEX command
    if (!$checkprivs = zen_check_database_privs('INDEX')) return sprintf(REASON_NO_PRIVILEGES,'INDEX');
    global $db;
    if (!zen_not_null($param)) return "Empty SQL Statement";
    $index = (strtoupper($param[1])=='INDEX') ? $param[2] : $param[3];
    if (in_array('USING',$param)) return 'USING parameter found. Cannot validate syntax. Please run manually in phpMyAdmin.';
    $table = (strtoupper($param[2])=='INDEX' && strtoupper($param[4])=='ON') ? $param[5] : $param[4];
    $sql = "show index from " . DB_PREFIX . $table;
    $result = $db->Execute($sql);
    while (!$result->EOF) {
      if (ZC_UPG_DEBUG3==true) echo $result->fields['Key_name'].'<br />';
      if (strtoupper($result->fields['Key_name']) == strtoupper($index)) {
        return sprintf(REASON_INDEX_ALREADY_EXISTS,$index,$table);
      }
      $result->MoveNext();
    }
/*
 * @TODO: verify that individual columns exist, by parsing the index_col_name parameters list
 *        Structure is (colname(len)),
 *                  or (colname),
 */
  }

  function zen_check_alter_command($param) {
    global $db;
    if (!zen_not_null($param)) return "Empty SQL Statement";
    if (!$checkprivs = zen_check_database_privs('ALTER')) return sprintf(REASON_NO_PRIVILEGES,DB_SERVER_USERNAME, DB_SERVER, 'ALTER');
    if (!$tbl_exists = zen_table_exists($param[2])) return sprintf(REASON_TABLE_NOT_FOUND,$param[2]).' CHECK PREFIXES!';
    switch (strtoupper($param[3])) {
      case ("ADD"):
        if (strtoupper($param[4]) == 'INDEX') {
          // check that the index to be added doesn't already exist
          $index = $param[5];
          $sql = "show index from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo 'KEY: '.$result->fields['Key_name'].'<br />';
            if  ($result->fields['Key_name'] == $index) {
              return sprintf(REASON_INDEX_ALREADY_EXISTS,$index,$param[2]);
            }
            $result->MoveNext();
          }
        } elseif (strtoupper($param[4])=='PRIMARY') {
          // check that the primary key to be added doesn't exist
          if ($param[5] != 'KEY') return;
          $sql = "show index from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Key_name'].'<br />';
            if  ($result->fields['Key_name'] == 'PRIMARY') {
              return sprintf(REASON_PRIMARY_KEY_ALREADY_EXISTS,$param[2]);
            }
            $result->MoveNext();
          }

        } elseif (!in_array(strtoupper($param[4]),array('CONSTRAINT','UNIQUE','PRIMARY','FULLTEXT','FOREIGN','SPATIAL') ) ) {
        // check that the column to be added does not exist
          $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
          $sql = "show fields from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Field'].'<br />';
            if  ($result->fields['Field'] == $colname) {
              return sprintf(REASON_COLUMN_ALREADY_EXISTS,$colname);
            }
            $result->MoveNext();
          }

        } elseif (strtoupper($param[5])=='AFTER') {
          // check that the requested "after" field actually exists
          $colname = ($param[6]=='COLUMN') ? $param[7] : $param[6];
          $sql = "show fields from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Field'].'<br />';
            if  ($result->fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
            $result->MoveNext();
          }

        } elseif (strtoupper($param[6])=='AFTER') {
          // check that the requested "after" field actually exists
          $colname = ($param[7]=='COLUMN') ? $param[8] : $param[7];
          $sql = "show fields from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Field'].'<br />';
            if  ($result->fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
            $result->MoveNext();
          }
/*
 * @TODO -- add check for FIRST parameter, to check that the FIRST colname specified actually exists
 */
        }
        break;
      case ("DROP"):
        if (strtoupper($param[4]) == 'INDEX') {
          // check that the index to be dropped exists
          $index = $param[5];
          $sql = "show index from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Key_name'].'<br />';
            if  ($result->fields['Key_name'] == $index) {
              return; // exists, so return with no error
            }
            $result->MoveNext();
          }
          // if we get here, then the index didn't exist
          return sprintf(REASON_INDEX_DOESNT_EXIST_TO_DROP,$index,$param[2]);

        } elseif (strtoupper($param[4])=='PRIMARY') {
          // check that the primary key to be dropped exists
          if ($param[5] != 'KEY') return;
          $sql = "show index from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Key_name'].'<br />';
            if  ($result->fields['Key_name'] == 'PRIMARY') {
              return; // exists, so return with no error
            }
            $result->MoveNext();
          }
          // if we get here, then the primary key didn't exist
          return sprintf(REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP,$param[2]);

        } elseif (!in_array(strtoupper($param[4]),array('CONSTRAINT','UNIQUE','PRIMARY','FULLTEXT','FOREIGN','SPATIAL'))) {
          // check that the column to be dropped exists
          $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
          $sql = "show fields from " . DB_PREFIX . $param[2];
          $result = $db->Execute($sql);
          while (!$result->EOF) {
            if (ZC_UPG_DEBUG3==true) echo $result->fields['Field'].'<br />';
            if  ($result->fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
            $result->MoveNext();
          }
          // if we get here, then the column didn't exist
          return sprintf(REASON_COLUMN_DOESNT_EXIST_TO_DROP,$colname);
        }//endif 'DROP'
        break;
      case ("ALTER"):
      case ("MODIFY"):
      case ("CHANGE"):
        // just check that the column to be changed 'exists'
        $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
        $sql = "show fields from " . DB_PREFIX . $param[2];
        $result = $db->Execute($sql);
        while (!$result->EOF) {
          if (ZC_UPG_DEBUG3==true) echo 'Field: ' . $result->fields['Field'].'<br />';
          if  ($result->fields['Field'] == $colname) {
            if (ZC_UPG_DEBUG3==true) echo '**FOUND**<br />';
            return; // exists, so return with no error
          }
          $result->MoveNext();
        }
        if (ZC_UPG_DEBUG3==true) echo '******NOT FOUND (' . $colname . ') ******<br />';
        // if we get here, then the column didn't exist
        return sprintf(REASON_COLUMN_DOESNT_EXIST_TO_CHANGE,$colname);
        break;
      default:
        // if we get here, then we're processing an ALTER command other than what we're checking for, so let it be processed.
        return;
        break;
    } //end switch
  }

  function zen_check_config_key($line) {
    global $db;
    $values=array();
    $values=explode("'",$line);
     //INSERT INTO configuration blah blah blah VALUES ('title','key', blah blah blah);
     //[0]=INSERT INTO.....
     //[1]=title
     //[2]=,
     //[3]=key
     //[4]=blah blah
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . DB_PREFIX . "configuration where configuration_key='".$key."'";
    $result = $db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIG_KEY_ALREADY_EXISTS,$key);
  }

  function zen_check_product_type_layout_key($line) {
    global $db;
    $values=array();
    $values=explode("'",$line);
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . DB_PREFIX . "product_type_layout where configuration_key='".$key."'";
    $result = $db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS,$key);
  }

  function zen_check_cfggroup_key($line) {
    global $db;
    $values=array();
    $values=explode("'",$line);
    $id = $values[1];
    $title  =  $values[3];
    $sql = "select configuration_group_title from " . DB_PREFIX . "configuration_group where configuration_group_title='".$title."'";
    $result = $db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIGURATION_GROUP_KEY_ALREADY_EXISTS,$title);
    $sql = "select configuration_group_title from " . DB_PREFIX . "configuration_group where configuration_group_id='".$id."'";
    $result = $db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIGURATION_GROUP_ID_ALREADY_EXISTS,$id);
  }

  function zen_write_to_upgrade_exceptions_table($line, $reason, $sql_file) {
    global $db;
    zen_create_exceptions_table();
    $sql="INSERT INTO " . DB_PREFIX . TABLE_UPGRADE_EXCEPTIONS . " VALUES (0,:file:, :reason:, now(), :line:)";
    $sql = $db->bindVars($sql, ':file:', $sql_file, 'string');
    $sql = $db->bindVars($sql, ':reason:', $reason, 'string');
    $sql = $db->bindVars($sql, ':line:', $line, 'string');
    if (ZC_UPG_DEBUG3==true) echo '<br />sql='.$sql.'<br />';
    $result = $db->Execute($sql);
    return $result;
  }

  function zen_purge_exceptions_table() {
    global $db;
    zen_create_exceptions_table();
    $result = $db->Execute("TRUNCATE TABLE " . DB_PREFIX . TABLE_UPGRADE_EXCEPTIONS );
    return $result;
  }

  function zen_create_exceptions_table() {
    global $db;
    if (!zen_table_exists(TABLE_UPGRADE_EXCEPTIONS)) {
      $result = $db->Execute("CREATE TABLE " . DB_PREFIX . TABLE_UPGRADE_EXCEPTIONS ." (
            upgrade_exception_id smallint(5) NOT NULL auto_increment,
            sql_file varchar(50) default NULL,
            reason varchar(200) default NULL,
            errordate datetime default '0001-01-01 00:00:00',
            sqlstatement text, PRIMARY KEY  (upgrade_exception_id)
          )");
    return $result;
    }
  }

  function zen_check_exceptions($result, $line) {
    // note: table-prefixes are ignored here, since they are not added if this is an exception
    //echo '<br /><strong>RESULT_CODE: </strong>' . $result . '<br /><strong>LINE:</strong>' . $line;
    if (strstr($result,'EZ-Pages Settings') && strstr(strtolower($line), 'insert into configuration_group')) return true;
    if (strstr($result,'DEFINE_SITE_MAP_STATUS') && strstr(strtolower($line), 'insert into configuration')) return true;
    //echo '<br /><strong>NO EXCEPTIONS </strong>TO IGNORE<br />';
  }

  function zcInstallAddSID($connection = '') {
    global $request_type, $session_started, $http_domain, $https_domain;
    $sid = '';
    if ($connection == '') $connection = $request_type;
    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ($session_started == true) {
      if (defined('SID') && zen_not_null(constant('SID'))) {
        $sid = SID;
      } elseif ( ($request_type == 'NONSSL' && $connection == 'SSL') || ($request_type == 'SSL' && $connection == 'NONSSL') ) {
        if ($http_domain != $https_domain) {
          $sid = zen_session_name() . '=' . zen_session_id();
        }
      }
    }
    return ($sid == '') ? '' : '&' . zen_output_string($sid);
  }

////
  function zen_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = zen_rand(0,9);
      } else {
        $char = chr(zen_rand(0,255));
      }
      if ($type == 'mixed') {
        if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (preg_match('/^[0-9]$/', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
  }
