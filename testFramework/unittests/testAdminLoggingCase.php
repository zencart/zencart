<?php
/**
 * File contains test cases for the admin activity logging infrastructure
 *
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

require_once('support/zcAdminTestCase.php');
/**
 * Testing Library
 */
class testAdminLoggingCase extends zcAdminTestCase
{
    public function setUp()
    {
      parent::setUp();
      require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogEventListener.php';
      require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterTextfile.php';
      require DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterDatabase.php';
      vfsStreamWrapper::register();
      vfsStream::useDotFiles(false);
      $_SESSION['securityToken'] = 'abc';
      $_SESSION['admin_id'] = 0;
      $_SERVER['REMOTE_ADDR'] = 'localhost';
      global $PHP_SELF;
      $PHP_SELF = 'testsuite';
      define('WARNING_REVIEW_ROGUE_ACTIVITY', 'Warning: review rogue activity');
    }

    public function testInstantiateLogEventListener()
    {
        $observer = new zcObserverLogEventListener(new notifier);
        $this->assertTrue($observer instanceof zcObserverLogEventListener);
    }

    public function testFilterArrayElements()
    {
      $observer = new zcObserverLogEventListener(new notifier);
      $data = array('x' => 'abc', 'password' => 'abc');
      $result = $observer::filterArrayElements($data);
      $this->assertFalse(strpos(print_r($result, true), 'abc'));
      $this->assertFalse(isset($result['x']));
      $this->assertFalse(isset($result['password']));
    }

    public function testEnsureDataIsUtf8()
    {
      define('CHARSET', 'iso-8859-1');
      $data = array('key1' => 'abc',
                    'key2' => iconv('UTF-8', 'ISO-8859-1', 'façade'),
                    'key3' => array('r'=>iconv('UTF-8', 'ISO-8859-1', 'égale')));
      $observer = new zcObserverLogEventListener(new notifier);
      $result = $observer::ensureDataIsUtf8($data);
      $this->assertTrue($result['key2'] == 'façade');
      $this->assertTrue($result['key3']['r'] == 'égale');
    }

    public function testParseForMaliciousContent()
    {
      define('CHARSET', 'utf-8');
      $data = 'This is malicious <script>alert(123);</script> code.';
      $observer = new zcObserverLogEventListener(new notifier);
      $result = $observer::parseForMaliciousContent($data);

      $this->assertTrue($result != false);
      $this->assertFalse(strpos($result, '<script>'));
    }

    public function testPrepareLogData()
    {
      define('CHARSET', 'utf-8');
      $message_to_log = 'abcdefg';
      $message_to_log = array('field1' => 'abcdefg');
      $requested_severity = 'warning';
      $_POST = array('name'=>'x', 'desc'=>'y');
      $observer = new zcObserverLogEventListener(new notifier);
      $result = $observer::prepareLogdata($message_to_log, $requested_severity);

      $this->assertTrue($result['severity'] == 'warning');
      $this->assertTrue(strpos($result['specific_message'], 'abcdefg') > 10);
    }

    public function testPrepareMaliciousLogData()
    {
      define('CHARSET', 'utf-8');
      $_SERVER['REMOTE_ADDR'] = 'localhost';
      $message_to_log = 'bad <iframe>';
      $requested_severity = 'info';
      $_POST = array('name'=>'risky <script> content', 'desc'=>'yes');
      // set up the logWriter dependencies
      $observer = new zcObserverLogWriterTextfile(new notifier);
      $structure = array(
              'logDir' => array(
                      'admin_log.txt' => '',
              )
      );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');
      $observer->setLogFilename($file);

      // test for expected notice
      $observer2 = new zcObserverLogEventListener(new notifier);
      $result = $observer2->updateNotifyAdminActivityLogEvent(new stdClass(), '', $message_to_log, $requested_severity);

      // and test that the malicious code doesn't appear in the log
      $var = file($file);
      $line = sizeof($var)-1;
      $this->assertTrue(substr($var[$line], 0, 6) == 'notice'); // should see notify instead of info
      $this->assertTrue(strpos($var[$line], '[&lt;iframe]') > 10); // should find converted iframe tag
      $this->assertTrue(strpos($var[$line], '[&lt;script]') > 10); // should find the script tag converted
      $this->assertTrue(strpos($var[$line], WARNING_REVIEW_ROGUE_ACTIVITY) > 10); // should find this warning
    }

    public function testUpdateNotifyAdminActivityLogEvent()
    {
      define('CHARSET', 'utf-8');
      $message_to_log = '';
      $requested_severity = 'warning';
      $_POST = array('name'=>'x', 'desc'=>'y');

      // set up the logWriter dependencies
      $observer = new zcObserverLogWriterTextfile(new notifier);
      $structure = array(
              'logDir' => array(
                      'admin_log.txt' => '',
              )
      );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');
      $observer->setLogFilename($file);
      $this->assertTrue(file_exists($file));

      // now trigger the notifier
      $observer = new zcObserverLogEventListener(new notifier);
      $result = $observer->updateNotifyAdminActivityLogEvent(new stdClass(), '', $message_to_log, $requested_severity);

      // and test that the message appears in the log
      $var = file($file);
      $line = sizeof($var)-1;
      $this->assertTrue(strpos($var[$line], 'Accessed page [testsuite]') > 10); // should find the message
    }

    /* filewriter */

    public function testFileLogWriterInstantiation()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);
      $this->assertTrue($observer instanceof zcObserverLogWriterTextfile);
    }

    public function testFileLogWriterInitLogFile()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
            // not giving a filename this time
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);
      $result = $observer->initLogFile();

      $this->assertTrue(file_exists($file));

      $var = file($file);
      $this->assertTrue(substr($var[0], 0, 6) == 'notice');
      $this->assertTrue(strpos($var[0], 'Logging started.') > 125); // should appear around position 130 depending on the date and ip address
    }

    public function testFileLogWriterInitEmptyLogFile()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
              'admin_log.txt' => '',
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);
      $result = $observer->initLogFile();

      $this->assertTrue(file_exists($file));

      $var = file($file);
      $this->assertTrue(substr($var[0], 0, 6) == 'notice');
      $this->assertTrue(strpos($var[0], 'Logging started.') > 125); // should appear around position 130 depending on the date and ip address
    }

    public function testFileLogWriterReset()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
              'admin_log.txt' => 'This is dummy data which should disappear during reset',
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);
      $observer->updateNotifyAdminFireLogWriterReset();

      $this->assertTrue(file_exists($file));

      $var = file($file);
      $this->assertTrue(sizeof($var) == 1);
      $this->assertFalse(strpos($var[0], 'dummy data')); // should not find the dummy data
      $this->assertTrue(strpos($var[0], 'Log reset by') > 1); // should find the reset notice
    }

    public function testFileLogWriterResetViaListener()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
              'admin_log.txt' => 'This is dummy data which should disappear during reset',
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);
      $observer2 = new zcObserverLogEventListener(new notifier);
      $observer2->updateNotifyAdminActivityLogReset();
      $this->assertTrue(file_exists($file));
      $var = file($file);
      $this->assertTrue(sizeof($var) == 1);
      $this->assertFalse(strpos($var[0], 'dummy data')); // should not find the dummy data
      $this->assertTrue(strpos($var[0], 'Log reset by') > 1); // should find the reset notice
    }

    public function testFileLogWriterUpdate()
    {
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
              'admin_log.txt' => 'placeholder',
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);

      $data = array('severity' => 'warning', 'ip_address' => 'localhost', 'page_accessed' => 'testLogWriterUpdate');

      $observer->updateNotifyAdminFireLogWriters(new stdClass(), '', $data);
      $this->assertTrue(file_exists($file));

      $var = file($file);
      $line = sizeof($var)-1;

      $this->assertTrue(sizeof($var) > 1);
      $this->assertFalse(strpos($var[0], 'Log reset')); // should not find a reset message

      $this->assertTrue(substr($var[$line], 0, strlen($data['severity'])) == 'warning'); // should find the severity level
      $this->assertTrue(strpos($var[$line], 'testLogWriterUpdate') > 10); // should find the message
    }

    public function testHelperFunctionZenRecordAdminActivity()
    {
      global $zco_notifier;
      $zco_notifier = new notifier;
      define('CHARSET', 'utf-8');
      $message = '1abcdefgh';
      $severity = 'critical';
      $observer = new zcObserverLogWriterTextfile(new notifier);

      $structure = array(
        'logDir' => array(
              'admin_log.txt' => 'placeholder',
           )
         );
      vfsStream::setup('_virtualroot_', '0777', $structure);
      $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

      $observer->setLogFilename($file);
      $observer2 = new zcObserverLogEventListener(new notifier);

      zen_record_admin_activity($message, $severity);

      $this->assertTrue(file_exists($file));

      $var = file($file);
      $line = sizeof($var)-1;

      $this->assertTrue(sizeof($var) > 1);
      $this->assertTrue(substr($var[$line], 0, strlen($severity)) == $severity); // test that the specified severity passes through
      $this->assertTrue(strpos($var[$line], $message) > 5); // should find the message
    }




    /* ********************************************************* */




    /** db writer **/
    public function testDbLogWriterInstantiation()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $observer = new zcObserverLogWriterDatabase(new notifier);
      $this->assertTrue($observer instanceof zcObserverLogWriterDatabase);
    }

    public function testDbPrepareLogData()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));
      $db->method('prepare_input')->will($this->returnArgument(0));

      global $PHP_SELF;
      $specific_message= 'This is a test message';
      $severity = 'warning';
      $postdata = json_encode(array('name'=>'x', 'desc'=>'y'));
      $flagged = false;
      $notes = false;

      $log_data = array(
              'event_epoch_time'=> time(),
              'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
              'page_accessed'   => basename($PHP_SELF) . (!isset($_SESSION['admin_id']) || (int)$_SESSION['admin_id'] == 0 ? ' ' . (isset($_POST['admin_name']) ? $_POST['admin_name'] : (isset($_POST['admin_email']) ? $_POST['admin_email'] : '') ) : ''),
              'page_parameters' => preg_replace('/(&amp;|&)$/', '', '&amp;item1=abc&amp;item2=defg'),
              'specific_message'=> $specific_message,
              'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
              'postdata'        => $postdata,
              'flagged'         => $flagged,
              'attention'       => ($notes === false ? '' : $notes),
              'severity'        => $severity,
      );

      $observer = new zcObserverLogWriterDatabase(new notifier);
      $result = $observer->dbPrepareLogData($log_data);

      $this->assertTrue($result['severity'] == $severity);
      $this->assertTrue($result['flagged'] == $flagged);
      $this->assertTrue($result['gzpost'] == gzdeflate($postdata, 7));
      $this->assertTrue($result['logmessage'] == $specific_message);
    }

    public function testPreserveSpecialCharacters()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $string = 'test1\ntest2';
      $observer = new zcObserverLogWriterDatabase(new notifier);
      $result = $observer->preserveSpecialCharacters($string);
      // note the test
      $this->assertTrue($result == "test1\ntest2");
    }

    public function testDbWriterInitLogsTable()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array());
      $db->method('execute')
      ->will($this->returnValue($db));

      $observer = new zcObserverLogWriterDatabase(new notifier);
      $db->fields = array(array());
      $result = $observer->initLogsTable();
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
              'admin_id'        => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
              'page_accessed'   => basename($PHP_SELF) . (!isset($_SESSION['admin_id']) || (int)$_SESSION['admin_id'] == 0 ? ' ' . (isset($_POST['admin_name']) ? $_POST['admin_name'] : (isset($_POST['admin_email']) ? $_POST['admin_email'] : '') ) : ''),
              'page_parameters' => preg_replace('/(&amp;|&)$/', '', '&amp;item1=abc&amp;item2=defg'),
              'specific_message'=> $specific_message,
              'ip_address'      => substr($_SERVER['REMOTE_ADDR'],0,45),
              'postdata'        => $postdata,
              'flagged'         => $flagged,
              'attention'       => ($notes === false ? '' : $notes),
              'severity'        => $severity,
      );

      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $observer = new zcObserverLogWriterDatabase(new notifier);
      $result = $observer->updateNotifyAdminFireLogWriters(new stdClass(), '', $log_data);
    }

    public function testCheckLogSchema()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $observer = new zcObserverLogWriterDatabase(new notifier);

      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $result = $observer->checkLogSchema();
    }

    public function testUpdateNotifyAdminFireLogWriterReset()
    {
      require DIR_FS_ADMIN . '../includes/classes/db/mysql/query_factory.php';
      global $db;
      $db = $this->getMock('queryFactory');
      $db->fields = array(array('Field'=>'logmessage'), array('Field'=>'severity'));
      $db->method('execute')
      ->will($this->returnValue($db));

      $observer = new zcObserverLogWriterDatabase(new notifier);
      $result = $observer->updateNotifyAdminFireLogWriterReset();
    }

}

/**
 * Test Dummy
 */
if (!function_exists('zen_get_admin_name')) {
  function zen_get_admin_name() { return 'TestAdminName';}
}
define('TABLE_ADMIN_ACTIVITY_LOG', 'mocked_db_table_instance');




















