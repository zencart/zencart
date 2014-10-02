<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcAdminTestCase.php');

/**
 * Test Dummy
 */
if (!function_exists('zen_get_admin_name')) {
  function zen_get_admin_name() { return 'TestAdminName';}
}

/**
 * Testing Library
 */
class testDbLogging extends zcAdminTestCase
{

  function setUp()
  {
    parent::setUp();

    if (!defined('DB_CHARSET'))         define('DB_CHARSET', 'utf-8');
    if (!defined('SQL_CACHE_METHOD'))   define('SQL_CACHE_METHOD', 'none');
    if (!defined('DB_TYPE'))            define('DB_TYPE', 'mysql');
    if (!defined('DB_PREFIX'))          define('DB_PREFIX', '');

    if (!defined('DB_SERVER'))          define('DB_SERVER', 'localhost');
    if (!defined('DB_SERVER_USERNAME')) define('DB_SERVER_USERNAME', 'root');
    if (!defined('DB_SERVER_PASSWORD')) define('DB_SERVER_PASSWORD', '');
    if (!defined('DB_DATABASE'))        define('DB_DATABASE', 'zencart');
//     if (!defined('DB_PORT'))            define('DB_PORT', '3306');
//     if (!defined('DB_SOCKET'))          define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

    if (!defined('IS_ADMIN_FLAG')) {
      define('IS_ADMIN_FLAG', true);
    }

    require DIR_FS_ADMIN . '../includes/database_tables.php';
    require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';

    global $db;
    $db = new queryFactory();
    $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

    // Provision the database
    $this->createTableAdminActivityLog();

    require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogEventListener.php';
    require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterDatabase.php';
    $_SESSION['securityToken'] = 'abc';
    $_SESSION['admin_id'] = 0;
    $_SERVER['REMOTE_ADDR'] = 'localhost';
    global $PHP_SELF;
    $PHP_SELF = 'testsuite';
    define('WARNING_REVIEW_ROGUE_ACTIVITY', 'Warning: review rogue activity');
  }

  function tearDown()
  {
    global $db;
    $db->Execute("DROP TABLE " . TABLE_ADMIN_ACTIVITY_LOG);
  }

  private function createTableAdminActivityLog()
  {
    global $db;
    $db->Execute('DROP TABLE IF EXISTS ' . TABLE_ADMIN_ACTIVITY_LOG);

    $sql = "CREATE TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " (
        log_id bigint(15) NOT NULL auto_increment,
        access_date datetime NOT NULL default '0001-01-01 00:00:00',
        admin_id int(11) NOT NULL default '0',
        page_accessed varchar(80) NOT NULL default '',
        page_parameters text,
        ip_address varchar(45) NOT NULL default '',
        flagged tinyint NOT NULL default '0',
        attention varchar(255) NOT NULL default '',
        gzpost mediumblob,
        logmessage mediumtext NOT NULL default '',
        severity varchar(9) NOT NULL default 'info',
        PRIMARY KEY  (log_id),
        KEY idx_page_accessed_zen (page_accessed),
        KEY idx_access_date_zen (access_date),
        KEY idx_flagged_zen (flagged),
        KEY idx_ip_zen (ip_address),
        KEY idx_severity_zen (severity)
      ) ENGINE=MyISAM";
    $result = $db->Execute($sql);
  }

  public function testDbLogWriterInstantiation()
  {
    $observer = new zcObserverLogWriterDatabase(new notifier);
    $this->assertTrue($observer instanceof zcObserverLogWriterDatabase);
  }

  public function testDbWriterInitLogsTable()
  {
    $observer = new zcObserverLogWriterDatabase(new notifier);

    global $db;

    // A. truncate the table, then test whether the init message was inserted
    $sql = "TRUNCATE TABLE " . TABLE_ADMIN_ACTIVITY_LOG;
    $result = $db->Execute($sql);
    $observer->initLogsTable();
    $sql = "SELECT * FROM " . TABLE_ADMIN_ACTIVITY_LOG . " order by log_id limit 1";
    $result = $db->Execute($sql);
    $this->assertTrue($result->fields['logmessage'] == 'Log found to be empty. Logging started.');
  }

  public function testUpdateNotifyAdminFireDbLogWriter()
  {
    global $db;
    $specific_message= '';
    $severity = 'warning';
    $postdata = json_encode(array('name'=>'x', 'desc'=>'y'));
    $flagged = false;
    $notes = false;

    $log_data = array(
            'event_epoch_time'=> time(),
            'admin_id'        => 555,
            'page_accessed'   => 'dbtest',
            'page_parameters' => preg_replace('/(&amp;|&)$/', '', '&amp;item1=abc&amp;item2=defg'),
            'specific_message'=> $specific_message,
            'ip_address'      => 'localhost',
            'postdata'        => $postdata,
            'flagged'         => $flagged,
            'attention'       => ($notes === false ? '' : $notes),
            'severity'        => $severity,
    );

    $observer = new zcObserverLogWriterDatabase(new notifier);

    // A. test whether $postdata and $severity were written
    $observer->updateNotifyAdminFireLogWriters(new stdClass(), '', $log_data);
    $sql = "SELECT * FROM " . TABLE_ADMIN_ACTIVITY_LOG . " order by log_id desc limit 1";
    $result = $db->Execute($sql);
    $this->assertTrue($result->fields['gzpost'] == gzdeflate($log_data['postdata'], 7));
    $this->assertTrue($result->fields['severity'] == $severity);

    // B. test whether $specific_message does its different work correctly
    $specific_message = 'abcdefg12345';
    $log_data['specific_message'] = $specific_message;
    $observer->updateNotifyAdminFireLogWriters(new stdClass(), '', $log_data);
    $sql = "SELECT * FROM " . TABLE_ADMIN_ACTIVITY_LOG . " order by log_id desc limit 1";
    $result = $db->Execute($sql);
    $this->assertTrue($result->fields['logmessage'] == $specific_message);
  }

  public function testCheckLogSchema()
  {
    global $db;
    $observer = new zcObserverLogWriterDatabase(new notifier);

    // A. break the schema
    $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " DROP severity";
    $db->Execute($sql);
    $sql = "ALTER TABLE " . TABLE_ADMIN_ACTIVITY_LOG . " DROP logmessage";
    $db->Execute($sql);

    // B. test whether the fields were added correctly
    $observer->checkLogSchema();

    global $db;
    $sql = "show fields from " . TABLE_ADMIN_ACTIVITY_LOG;
    $result = $db->Execute($sql);

    $found_logmessage = $found_severity = false;
    foreach ($result as $row => $val) {
      if ($val['Field'] == 'logmessage') {
        $found_logmessage = true;
      }
      if ($val['Field'] == 'severity') {
        $found_severity = true;
      }
    }

    $this->assertTrue($found_logmessage);
    $this->assertTrue($found_severity);
  }

  public function testUpdateNotifyAdminFireLogWriterReset()
  {
    global $db;
    $observer = new zcObserverLogWriterDatabase(new notifier);
    $observer->updateNotifyAdminFireLogWriterReset();

    // test whether the table was properly truncated and the proper init records were written
    $sql = "select * from " . TABLE_ADMIN_ACTIVITY_LOG . " limit 10";
    $result = $db->Execute($sql);

    // A. there should be one record
    $this->assertTrue(count($result) == 1);

    // B. The first should be a "reset by"
    $this->assertTrue(substr($result->fields['logmessage'], 0, 13) == 'Log reset by ');
  }

}