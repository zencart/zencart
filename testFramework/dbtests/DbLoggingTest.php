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
    if (!defined('DB_SERVER'))          define('DB_SERVER', 'zen.local');
    if (!defined('DB_SERVER_USERNAME')) define('DB_SERVER_USERNAME', 'zencart');
    if (!defined('DB_SERVER_PASSWORD')) define('DB_SERVER_PASSWORD', 'zencart');
    if (!defined('DB_DATABASE'))        define('DB_DATABASE', 'zencart');
//     if (!defined('DB_PORT'))            define('DB_PORT', '3306');
//     if (!defined('DB_SOCKET'))          define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

    if (!defined('IS_ADMIN_FLAG')) {
      define('IS_ADMIN_FLAG', true);
    }

    parent::setUp();

    require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
    require(DIR_FS_ADMIN . '../includes/database_tables.php');

    global $db;
    $db = new queryFactory();
    $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

    //TODO:
    // provision the database, creating necessary tables




    require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogEventListener.php';
    require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterDatabase.php';
    $_SESSION['securityToken'] = 'abc';
    $_SESSION['admin_id'] = 0;
    $_SERVER['REMOTE_ADDR'] = 'localhost';
    global $PHP_SELF;
    $PHP_SELF = 'testsuite';
    define('WARNING_REVIEW_ROGUE_ACTIVITY', 'Warning: review rogue activity');
  }

  public function testDbLogWriterInstantiation()
  {
    $observer = new zcObserverLogWriterDatabase(new notifier);
    $this->assertTrue($observer instanceof zcObserverLogWriterDatabase);
  }

  public function testDbWriterInitLogsTable()
  {
    global $db;

    $observer = new zcObserverLogWriterDatabase(new notifier);
    $result = $observer->initLogsTable();

    // TODO: test whether the "init" message was stored
    // TODO -- should we truncate the table first? or make that a second test? and a third?
  }

  public function testUpdateNotifyAdminFireDbLogWriter()
  {
    global $PHP_SELF;
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
    $result = $observer->updateNotifyAdminFireLogWriters(new stdClass(), '', $log_data);

    // TODO - test whether $postdata and $severity were written
    // TODO - test whether $specific_message does its different work correctly
  }

  public function testCheckLogSchema()
  {

    // TODO - break the schema

    $observer = new zcObserverLogWriterDatabase(new notifier);
    $result = $observer->checkLogSchema();

    // TODO - test whether the fields were added correctly
  }

  public function testUpdateNotifyAdminFireLogWriterReset()
  {
    $observer = new zcObserverLogWriterDatabase(new notifier);
    $result = $observer->updateNotifyAdminFireLogWriterReset();

    // TODO - test whether the table was properly truncated and the proper init records were written
  }










}