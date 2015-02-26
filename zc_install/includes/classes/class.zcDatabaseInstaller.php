<?php
/**
 * file contains zcDatabaseInstaller Class
 * @package Installer
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0
 *
 */
/**
 *
 * zcDatabaseInstaller Class
 *
 */
class zcDatabaseInstaller
{
  public function __construct($options)
  {
    $this->func = create_function('$matches', 'return strtoupper($matches[1]);');
    $dbtypes = array();
    $path = DIR_FS_ROOT . 'includes/classes/db/';
    $dir = dir($path);
    while ($entry = $dir->read()) {
      if (is_dir($path . $entry) && substr($entry, 0,1) != '.') {
        $dbtypes[] = $entry;
      }
    }
    $dir->close();

    $this->dbHost = $options['db_host'];
    $this->dbUser = $options['db_user'];
    $this->dbPassword = $options['db_password'];
    $this->dbName = $options['db_name'];
    $this->dbPrefix = $options['db_prefix'];
    $this->dbCharset = $options['db_charset'];
    $this->dbType = in_array($options['db_type'], $dbtypes) ? $options['db_type'] : 'mysql';
    $this->dieOnErrors = isset($options['dieOnErrors']) ? (bool)$options['dieOnErrors'] : FALSE;
    $this->errors = array();
    $this->basicParseStrings = array(
    'DROP TABLE IF EXISTS ',
    'CREATE TABLE ',
    'REPLACE INTO ',
    'INSERT INTO ',
    'INSERT IGNORE INTO ',
    'ALTER IGNORE TABLE ',
    'ALTER TABLE ',
    'TRUNCATE TABLE ',
    'RENAME TABLE ',
    'TO ',
    'UPDATE ',
    'UPDATE IGNORE ',
    'DELETE FROM ',
    'DROP INDEX ',
    'LEFT JOIN ',
    'FROM ',

    );
  }
  public function getConnection()
  {
    require_once(DIR_FS_ROOT . 'includes/classes/db/' . $this->dbType . '/query_factory.php');
    $this->db = new queryFactory;
    $options = array('dbCharset'=>$this->dbCharset);
    $result = $this->db->Connect($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, 'false', $this->dieOnErrors, $options);
    return $result;
  }
  public function parseSqlFile($fileName, $options = NULL)
  {
    $this->extendedOptions = (isset($options)) ? $options : array();
    $lines = file($fileName);
    if (FALSE === $lines) {logDetails('COULD NOT OPEN FILE: ' . $fileName, $fileName); die('HERE_BE_MONSTERS - could not open file');}
    $this->fileName = $fileName;
    $this->upgradeExceptions = array();
    if (!isset($lines) || !is_array($lines)) {logDetails('HERE BE MONSTERS', $fileName); die('HERE_BE_MONSTERS');}
    $this->doJSONProgressLoggingStart(count($lines));
    $this->keepTogetherCount = 0;
    $this->newLine = "";
    foreach ($lines as $line)
    {
      $this->jsonProgressLoggingCount++;
      $this->processline($line);
    }
//if (count($lines) < 200) sleep(5);
    $this->doJsonProgressLoggingEnd();

    return false;
    /**
     * @todo further enhancement could add an advanced mode which returns the actual exceptions list to the browser.
     *       For now, since outputting them has usually just raised unnecessary questions and confusion for end-users, simply returning false to suppress their display.
     *       Advanced users/integrators can check the upgrade_exceptions database table or the /logs/ folder for the details.
     */
//    return (count($this->upgradeExceptions) > 0);
  }
  private function processLine($line)
  {
    $this->keepTogetherLines = 1;
    $this->line = trim($line);
    if (substr($this->line,0,28) == '#NEXT_X_ROWS_AS_ONE_COMMAND:') $this->keepTogetherLines = substr($this->line,28);
    if (substr($this->line,0,1) != '#' && substr($this->line,0,1) != '-' && $this->line != '')
    {
      $this->parseLineContent();
      $this->newLine .= $this->line . ' ';
      if ( substr($this->line,-1) ==  ';')
      {
        if (substr($this->newLine,-1)==' ') $this->newLine = substr($this->newLine,0,(strlen($this->newLine)-1));
        $this->keepTogetherCount++;
        if ($this->keepTogetherCount == $this->keepTogetherLines)
        {
          $this->completeLine = TRUE;
          $this->keepTogetherCount = 0;
          if (isset($this->collateSuffix) && $this->collateSuffix != '' && (!defined('IGNORE_DB_CHARSET') || (defined('IGNORE_DB_CHARSET') && IGNORE_DB_CHARSET != FALSE)))
          {
            $this->newLine = rtrim($this->newLine, ';') . $this->collateSuffix . ';';
            $this->collateSuffix = '';
          }
        } else
        {
          $this->completeLine = FALSE;
        }
      }
//      echo $this->newLine;
      if ($this->completeLine)
      {
        if (get_magic_quotes_runtime() > 0) $this->newLine = stripslashes($this->newLine);
        $output = (trim(str_replace(';','',$this->newLine)) != '' && !$this->ignoreLine) ? $this->tryExecute($this->newLine) : '';
        $this->doJsonProgressLoggingUpdate();
        $this->newLine = "";
        $this->ignoreLine = FALSE;
        $this->completeLine = FALSE;
        $this->keepTogetherLines = 1;
      }
    }
  }

  private function parseLineContent()
  {
    $this->lineSplit = explode(" ",(substr($this->line,-1)==';') ? substr($this->line,0,strlen($this->line)-1) : $this->line);
    if (!isset($this->lineSplit[4])) $this->lineSplit[4] = "";
    if (!isset($this->lineSplit[5])) $this->lineSplit[5] = "";
    foreach ($this->basicParseStrings as $parseString)
    {
      $parseMethod = 'parser' . trim($this->camelize($parseString));
      if (substr(strtoupper($this->line), 0, strlen($parseString)) == $parseString)
      {
//          echo 'GOT '. $parseMethod .  "<br>";
        if (method_exists($this, $parseMethod))
        {
          $this->$parseMethod();
        }
      }
    }
  }

  public function tryExecute($sql)
  {
//    echo $sql;
//    $this->writeUpgradeExceptions($this->line, '', $this->sqlFile);
//    logDetails($sql, $this->sqlFile);
    $result = $this->db->execute($sql);
    if (!$result)
    {
      //echo $this->db->errorText;
      $this->writeUpgradeExceptions($this->line, $this->db->error_text, $this->sqlFile);
    }
  }
  public function parserDropTableIfExists ()
  {
    $this->line = 'DROP TABLE IF EXISTS ' . $this->dbPrefix . substr($this->line, 21);
  }
  public function parserCreateTable()
  {
    $table = (strtoupper($this->lineSplit[2].' '.$this->lineSplit[3].' '.$this->lineSplit[4]) == 'IF NOT EXISTS') ? $this->lineSplit[5] : $this->lineSplit[2];
    if ($this->tableExists($table))
    {
      $this->ignoreLine = TRUE;
      if (strtoupper($this->lineSplit[2].' '.$this->lineSplit[3].' '.$this->lineSplit[4]) != 'IF NOT EXISTS')
      {
        $this->writeUpgradeExceptions($this->line, sprintf(REASON_TABLE_ALREADY_EXISTS, $table), $this->filename);
      }
    } else
    {
      $this->line = (strtoupper($this->lineSplit[2].' '.$this->lineSplit[3].' '.$this->lineSplit[4]) == 'IF NOT EXISTS') ? 'CREATE TABLE IF NOT EXISTS ' . $this->dbPrefix . substr($this->line, 27) : 'CREATE TABLE ' . $this->dbPrefix . substr($this->line, 13);
      $this->collateSuffix = (strtoupper($this->lineSplit[3]) == 'AS' || (isset($this->lineSplit[6]) && strtoupper($this->lineSplit[6]) == 'AS')) ? '' : ' COLLATE ' . $this->dbCharset . '_general_ci';
    }
  }
  public function parserInsertInto()
  {
    if (($this->lineSplit[2] == 'configuration'       && ($result = $this->checkConfigKey($this->line))) ||
        ($this->lineSplit[2] == 'product_type_layout' && ($result = $this->checkProductTypeLayoutKey($this->line))) ||
        ($this->lineSplit == 'configuration_group' && ($result = $this->checkCfggroupKey($line))) ||
        (!$this->tableExists($this->lineSplit[2])))
    {
      if (!isset($result)) $result = sprintf(REASON_TABLE_NOT_FOUND, $this->lineSplit[2]).' CHECK PREFIXES!';
      $this->writeUpgradeExceptions($this->line, $result, $this->fileName);
      $this->ignoreLine = true;
    } else
    {
      $this->line = 'INSERT INTO ' . $this->dbPrefix . substr($this->line, 12);
    }
  }
  public function parserReplaceInto()
  {
    if (($this->lineSplit[2] == 'configuration'       && ($result = $this->checkConfigKey($this->line))) ||
        ($this->lineSplit[2] == 'product_type_layout' && ($result = $this->checkProductTypeLayoutKey($this->line))) ||
        ($this->lineSplit == 'configuration_group' && ($result = $this->checkCfggroupKey($line))) ||
        (!$this->tableExists($this->lineSplit[2])))
    {
      if (!isset($result)) $result = sprintf(REASON_TABLE_NOT_FOUND, $this->lineSplit[2]).' CHECK PREFIXES!';
      $this->writeUpgradeExceptions($this->line, $result, $this->fileName);
      $this->ignoreLine = true;
    } else
    {
      $this->line = 'REPLACE INTO ' . $this->dbPrefix . substr($this->line, 12);
    }
  }
  public function parserUpdate()
  {
    if (!$this->tableExists($this->lineSplit[1]))
    {
      $this->writeUpgradeExceptions($this->line, $result, $this->fileName);
      $this->ignoreLine = true;
    } else
    {
      $this->line = 'UPDATE ' . $this->dbPrefix . substr($this->line, 7);
    }
  }
  public function parserAlterTable() {
    if(!$this->tableExists($this->lineSplit[2])) {
      $result = sprintf(REASON_TABLE_NOT_FOUND, $this->lineSplit[2]).' CHECK PREFIXES!';
      $this->writeUpgradeExceptions($this->line, $result, $this->filename);
    }
    else {
      $this->line = 'ALTER TABLE ' . $this->dbPrefix . substr($this->line, 12);

        switch(strtoupper($this->lineSplit[3])) {
          case 'ADD':
          case 'DROP':
            // Check to see if the column / index already exists
            $exists = false;
            switch(strtoupper($this->lineSplit[4])) {
              case 'COLUMN':
                $exists = $this->tableColumnExists($this->lineSplit[2], $this->lineSplit[5]);
                break;
              case 'INDEX':
              case 'KEY':
                // Do nothing if the index_name is ommitted
                if($this->lineSplit[5] != 'USING' && substr($this->lineSplit[5], 0, 1) != '(') {
                  $exists = $this->tableIndexExists($this->lineSplit[2], $this->lineSplit[5]);
                }
                break;
              case 'UNIQUE':
              case 'FULLTEXT':
              case 'SPATIAL':
                if($this->lineSplit[6] == 'INDEX' || $this->lineSplit[6] == 'KEY') {
                  // Do nothing if the index_name is ommitted
                  if($this->lineSplit[7] != 'USING' && substr($this->lineSplit[7], 0, 1) != '(') {
                    $exists = $this->tableIndexExists($this->lineSplit[2], $this->lineSplit[7]);
                  }
                }
                // Do nothing if the index_name is ommitted
                else if($this->lineSplit[6] != 'USING' && substr($this->lineSplit[6], 0, 1) != '('){
                  $exists = $this->tableIndexExists($this->lineSplit[2], $this->lineSplit[6]);
                }
                break;
              case 'CONSTRAINT':
              case 'PRIMARY':
              case 'FOREIGN':
                // Do nothing (no checks at this time)
                break;
              default:
                // No known item added, MySQL defaults to column definition
                $exists = $this->tableColumnExists($this->lineSplit[2], $this->lineSplit[4]);
            }
            // Ignore this line if the column / index already exists
            if($exists) $this->ignoreLine = true;

            break;
          default:
            // Do nothing
      }
    }
  }
  public function writeUpgradeExceptions($line, $message, $sqlFile)
  {
    logDetails($line . '  ' . $message . '  ' . $sqlFile, 'upgradeException');
    $this->upgradeExceptions[] = $message;
    $this->createExceptionsTable();
    $sql="INSERT INTO " . $this->dbPrefix . TABLE_UPGRADE_EXCEPTIONS . " VALUES (0,:file:, :reason:, now(), :line:)";
    $sql = $this->db->bindVars($sql, ':file:', $sqlFile, 'string');
    $sql = $this->db->bindVars($sql, ':reason:', $message, 'string');
    $sql = $this->db->bindVars($sql, ':line:', $line, 'string');
    $result = $this->db->Execute($sql);
    return $result;
  }
  public function createExceptionsTable()
  {
    if (!$this->tableExists(TABLE_UPGRADE_EXCEPTIONS))
    {
      $result = $this->db->Execute("CREATE TABLE " . $this->dbPrefix . TABLE_UPGRADE_EXCEPTIONS ." (
            upgrade_exception_id smallint(5) NOT NULL auto_increment,
            sql_file varchar(50) default NULL,
            reason varchar(200) default NULL,
            errordate datetime default '0001-01-01 00:00:00',
            sqlstatement text, PRIMARY KEY  (upgrade_exception_id)
          )");
    return $result;
    }
  }
  public function checkCfggroupKey($line)
  {
    $values=array();
    $values=explode("'",$line);
    $id = $values[1];
    $title  =  $values[3];
    $sql = "select configuration_group_title from " . $this->dbPrefix . "configuration_group where configuration_group_title='".$title."'";
    $result = $this->db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIGURATION_GROUP_KEY_ALREADY_EXISTS,$title);
    $sql = "select configuration_group_title from " . $this->dbPrefix . "configuration_group where configuration_group_id='".$id."'";
    $result = $this->db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIGURATION_GROUP_ID_ALREADY_EXISTS,$id);
    return FALSE;
  }
  public function checkProductTypeLayoutKey($line)
  {
    $values=array();
    $values=explode("'",$line);
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . $this->dbPrefix . "product_type_layout where configuration_key='".$key."'";
    $result = $this->db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS,$key);
    return FALSE;
  }

  public function checkConfigKey($line)
  {
    $values = array();
    $values = explode("'",$line);
     //INSERT INTO configuration blah blah blah VALUES ('title','key', blah blah blah);
     //[0]=INSERT INTO.....
     //[1]=title
     //[2]=,
     //[3]=key
     //[4]=blah blah
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . $this->dbPrefix . "configuration where configuration_key='".$key."'";
    $result = $this->db->Execute($sql);
    if ($result->RecordCount() >0 ) return sprintf(REASON_CONFIG_KEY_ALREADY_EXISTS,$key);
    return FALSE;
  }
  public function tableExists($table)
  {
    $tables = $this->db->Execute("SHOW TABLES like '" . $this->dbPrefix . $table . "'");
    if ($tables->RecordCount() > 0)
    {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  public function tableColumnExists($table, $column)
  {
    $check = $this->db->Execute(
      'SHOW COLUMNS FROM `' . DB_DATABASE . '`.`' . $this->db->prepare_input($table) . '` ' .
      'WHERE `Field` = \'' . $this->db->prepare_input($column) . '\''
    );
    return !$check->EOF;
  }
  public function tableIndexExists($table, $index)
  {
    $check = $this->db->Execute(
      'SHOW INDEX FROM `' . DB_DATABASE . '`.`' . $this->db->prepare_input($table) . '` ' .
      'WHERE `Key_name` = \'' . $this->db->prepare_input($index) . '\''
    );
    return !$check->EOF;
  }
  public function updateConfigKeys()
  {
    $sql = "update ". $this->dbPrefix ."configuration set configuration_value='". $this->sqlCacheDir ."' where configuration_key = 'SESSION_WRITE_DIRECTORY'";
    $this->db->Execute($sql);
    $sql = "update ". $this->dbPrefix ."configuration set configuration_value='". preg_replace('~/cache$~', '/logs', $this->sqlCacheDir) ."/page_parse_time.log' where configuration_key = 'STORE_PAGE_PARSE_TIME_LOG'";
    $this->db->Execute($sql);
    if (isset($_POST['http_server_catalog']) && $_POST['http_server_catalog'] != '') {
      $email_stub = preg_replace('~.*\/\/(www.)*~', 'YOUR_EMAIL@', $_POST['http_server_catalog']);
      $sql = "update ". $this->dbPrefix ."configuration set configuration_value=:emailStub: where configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM')";
      $sql = $this->db->bindVars($sql, ':emailStub:', $email_stub, 'string');
      $this->db->Execute($sql);
    }
    return FALSE;
  }
  public function doCompletion($options)
  {
    global $request_type;
    if ($request_type == 'SSL') {
      $sql = "UPDATE " . $this->dbPrefix . "configuration set configuration_value = '1:1', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
      $this->db->Execute($sql);
    }
    $sql = "update " . $this->dbPrefix . "admin set admin_name = '" . $options['admin_user'] . "', admin_email = '" . $options['admin_email'] . "', admin_pass = '" . zen_encrypt_password($options['admin_password']) . "', pwd_last_change_date = " . ($request_type == 'SSL' ? 'NOW()' : 'timestamp("1970-01-01 00:00:00")') . ($request_type == 'SSL' ? '' : ", reset_token = '" . (time() + (72 * 60 * 60)) . "}" . zen_encrypt_password($options['admin_password']) . "'") . " where admin_id = 1";
    $this->db->Execute($sql);

// @TODO temporarily disabled
// enable/disable automatic version-checking
//    $sql = "update " . $this->dbPrefix . "configuration set configuration_value = '".($this->configInfo['check_for_updates'] ? 'true' : 'false' ) ."' where configuration_key = 'SHOW_VERSION_UPDATE_IN_HEADER'";
//    $this->db->Execute($sql);
  }
  private function camelize($parseString)
  {
    $parseString = preg_replace_callback('/\s([0-9,a-z])/', $this->func, strtolower($parseString));
    $parseString[0] = strtoupper($parseString[0]);
    return $parseString;
  }
  private function doJsonProgressLoggingUpdate()
  {
    if (isset($this->extendedOptions['doJsonProgressLogging']))
    {
      $fileName = $this->extendedOptions['doJsonProgressLoggingFileName'];
      $progress = ($this->jsonProgressLoggingCount/$this->jsonProgressLoggingTotal*100);
      $fp = fopen($fileName, "w");
      if ($fp)
      {
        $arr = array('total'=>'0', 'progress'=>$progress, 'message'=>$this->extendedOptions['message']);
        fwrite($fp, json_encode($arr));
        fclose($fp);
      }
    }
  }
  private function doJsonProgressLoggingStart($count)
  {
    if (isset($this->extendedOptions['doJsonProgressLogging']))
    {
      $this->jsonProgressLoggingTotal = $count;
      $this->jsonProgressLoggingCount = 0;
      $fileName = $this->extendedOptions['doJsonProgressLoggingFileName'];
      $fp = fopen($fileName, "w");
      if ($fp)
      {
        $arr = array('total'=>$count, 'progress'=>0, 'message'=>$this->extendedOptions['message']);
        fwrite($fp, json_encode($arr));
        fclose($fp);
      }
    }
  }
  private function doJsonProgressLoggingEnd()
  {
    if (isset($this->extendedOptions['doJsonProgressLogging']))
    {
      $this->jsonProgressLoggingCount = 0;
      $fileName = $this->extendedOptions['doJsonProgressLoggingFileName'];
      $fp = fopen($fileName, "w");
      if ($fp)
      {
        $arr = array('total'=>'0', 'progress'=>100, 'message'=>$this->extendedOptions['message']);
        fwrite($fp, json_encode($arr));
        fclose($fp);
      }
    }
  }
}
