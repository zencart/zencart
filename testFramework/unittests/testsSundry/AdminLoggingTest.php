<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

require_once(__DIR__ . '/../support/zcTestCase.php');
/**
 * Test Dummy
 */
if (!function_exists('zen_get_admin_name')) {
    function zen_get_admin_name()
    {
        return 'TestAdminName';
    }
}

/**
 * Testing Library
 */
class testAdminLoggingCase extends zcTestCase
{
    protected $preserveGlobalState = FALSE;
    protected $runTestInSeparateProcess = TRUE;

    public function setUp()
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php';
        require_once DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogEventListener.php';
        require_once DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterTextfile.php';
        require_once DIR_FS_ADMIN . 'includes/classes/class.admin.zcObserverLogWriterDatabase.php';
        vfsStreamWrapper::register();
        vfsStream::useDotFiles(false);
        $_SESSION['securityToken'] = 'abc';
        $_SESSION['admin_id'] = 0;
        $_SERVER['REMOTE_ADDR'] = 'localhost';
        global $PHP_SELF;
        $PHP_SELF = 'testsuite';
        define('WARNING_REVIEW_ROGUE_ACTIVITY', 'Warning: review rogue activity');
    }

    private function initTestFileSystem($hasFile = true, $contents = '')
    {
        $file = ($hasFile) ? array('admin_log.txt' => $contents) : array();
        $structure = array(
            'logDir' => $file
        );
        vfsStream::setup('_virtualroot_', '0777', $structure);
        $file = vfsStream::url('_virtualroot_/logDir/admin_log.txt');

        return $file;
    }

    public function testInstantiateLogEventListener()
    {
        $observer = new zcObserverLogEventListener();
        $this->assertInstanceOf('zcObserverLogEventListener', $observer);
    }

    public function testFilterArrayElements()
    {
        $observer = new zcObserverLogEventListener();
        $data = array('x' => 'abc', 'password' => 'abc');
        $result = $observer::filterArrayElements($data);
        $this->assertNotContains('abc', print_r($result, true));
        $this->assertNotContains('x', $result);
        $this->assertNotContains('password', $result);
    }

    public function testEnsureDataIsUtf8()
    {
        define('CHARSET', 'iso-8859-1');
        $data = array(
            'key1' => 'abc',
            'key2' => iconv('UTF-8', 'ISO-8859-1', 'façade'),
            'key3' => array('r' => iconv('UTF-8', 'ISO-8859-1', 'égale'))
        );
        $observer = new zcObserverLogEventListener();
        $result = $observer::ensureDataIsUtf8($data);
        $this->assertEquals($result['key2'], 'façade');
        $this->assertEquals($result['key3']['r'], 'égale');
    }

    public function testParseForMaliciousContent()
    {
        define('CHARSET', 'utf-8');
        $data = 'This is malicious <script>alert(123);</script> code.';
        $observer = new zcObserverLogEventListener();
        $result = $observer::parseForMaliciousContent($data);

        $this->assertNotFalse($result, 'Should not return false');
        $this->assertNotContains('<script>', $result, 'Should be escaped');
    }

    public function testPrepareLogData()
    {
        define('CHARSET', 'utf-8');
        $message_to_log = 'abcdefg';
        $message_to_log = array('field1' => 'abcdefg');
        $requested_severity = 'warning';
        $_POST = array('name' => 'x', 'desc' => 'y');
        $observer = new zcObserverLogEventListener();
        $result = $observer::prepareLogdata($message_to_log, $requested_severity);

        $this->assertEquals($result['severity'], 'warning');
        $this->assertContains('abcdefg', $result['specific_message']);
    }

    public function testPrepareMaliciousLogData()
    {
        define('CHARSET', 'utf-8');
        $_SERVER['REMOTE_ADDR'] = 'localhost';
        $message_to_log = 'bad <iframe>';
        $requested_severity = 'info';
        $_POST = array('name' => 'risky <script> content', 'desc' => 'yes');
        // set up the logWriter dependencies
        $file = $this->initTestFileSystem(true);
        $observer = new zcObserverLogWriterTextfile($file);

        // test for expected notice
        $observer2 = new zcObserverLogEventListener();
        $stdClass = new stdClass();
        $result = $observer2->updateNotifyAdminActivityLogEvent($stdClass, '', $message_to_log, $requested_severity);

        // and test that the malicious code doesn't appear in the log
        $var = file($file);
        $line = sizeof($var) - 1;
        $this->assertEquals('notice', substr($var[$line], 0, 6), 'should see notify instead of info');
        $this->assertContains('[&lt;iframe]', $var[$line], 'should find converted iframe tag');
        $this->assertContains('[&lt;script]', $var[$line], 'should find the script tag converted');
        $this->assertContains(WARNING_REVIEW_ROGUE_ACTIVITY, $var[$line], 'warning should be present');
    }

    public function testUpdateNotifyAdminActivityLogEvent()
    {
        define('CHARSET', 'utf-8');
        $message_to_log = '';
        $requested_severity = 'warning';
        $_POST = array('name' => 'x', 'desc' => 'y');

        // set up the logWriter dependencies
        $file = $this->initTestFileSystem();
        $observer = new zcObserverLogWriterTextfile($file);
        $this->assertFileExists($file);

        // now trigger the notifier
        $observer = new zcObserverLogEventListener();
        $stdClass = new stdClass();
        $result = $observer->updateNotifyAdminActivityLogEvent($stdClass, '', $message_to_log, $requested_severity);

        // and test that the message appears in the log
        $var = file($file);
        $line = sizeof($var) - 1;
        $this->assertContains('Accessed page [testsuite]', $var[$line]); // should find the message
    }

    /* filewriter */

    public function testFileLogWriterInstantiation()
    {
        $observer = new zcObserverLogWriterTextfile();
        $this->assertInstanceOf('zcObserverLogWriterTextfile', $observer);
    }


    public function testFileLogWriterUpdateToEmptyLogFile()
    {
        $file = $this->initTestFileSystem(true, '');
        $data = array('severity' => 'warning', 'ip_address' => 'localhost', 'page_accessed' => 'testEmptyLogfile');

        $observer = new zcObserverLogWriterTextfile($file);
        $stdClass = new stdClass();
        $observer->updateNotifyAdminFireLogWriters($stdClass, '', $data);
        $this->assertFileExists($file);

        $var = file($file);
        $line = sizeof($var) - 1;

        $this->assertEquals(2, sizeof($var), 'Should have more than two rows: the reset row plus a data row');
        $this->assertEquals(substr($var[0], 0, 6), 'notice', 'The init record should be notice');
        $this->assertContains('Logging started.', $var[0], 'Should find an init message on first row');
        $this->assertNotContains('Logging started.', $var[1], 'Should not find an init on the next row');
        $this->assertNotContains('Log reset', $var[1], 'Should find an init on the next row');

        $this->assertEquals(substr($var[$line], 0, strlen($data['severity'])), 'warning'); // should find the severity level
        $this->assertContains('testEmptyLogfile', $var[$line]); // should find the message
    }

    public function testFileLogWriterUpdateToMissingLogFile()
    {
        $file = $this->initTestFileSystem(false);
        $data = array('severity' => 'warning', 'ip_address' => 'localhost', 'page_accessed' => 'testMissingLogfile');

        $observer = new zcObserverLogWriterTextfile($file);
        $stdClass = new stdClass();
        $observer->updateNotifyAdminFireLogWriters($stdClass, '', $data);

        $this->assertFileExists($file, 'File must exist.');

        $var = file($file);
        $line = sizeof($var) - 1;

        $this->assertEquals(2, sizeof($var), 'Should have more than two rows: the reset row plus a data row');
        $this->assertEquals(substr($var[0], 0, 6), 'notice', 'The init record should be notice');
        $this->assertContains('Logging started.', $var[0], 'Should find an init message on first row');
        $this->assertNotContains('Logging started.', $var[1], 'Should not find an init on the next row');
        $this->assertNotContains('Log reset', $var[1], 'Should find an init on the next row');

        $this->assertEquals(substr($var[$line], 0, strlen($data['severity'])), 'warning'); // should find the severity level
        $this->assertContains('testMissingLogfile', $var[$line]); // should find the message
    }


    public function testFileLogWriterUpdateToRegularLogFile()
    {
        $file = $this->initTestFileSystem(true, 'placeholder');
        $data = array('severity' => 'warning', 'ip_address' => 'localhost', 'page_accessed' => 'testLogWriterUpdate');

        $observer = new zcObserverLogWriterTextfile($file);
        $stdClass = new stdClass();
        $observer->updateNotifyAdminFireLogWriters($stdClass, '', $data);
        $this->assertFileExists($file);

        $var = file($file);
        $line = sizeof($var) - 1;

        $this->assertGreaterThan(1, sizeof($var));
        $this->assertNotContains('Log reset', $var[0]); // should not find a reset message

        $this->assertEquals(substr($var[$line], 0, strlen($data['severity'])), 'warning'); // should find the severity level
        $this->assertContains('testLogWriterUpdate', $var[$line]); // should find the message
    }

    public function testFileLogWriterReset()
    {
        $file = $this->initTestFileSystem(true, 'This is dummy data which should disappear during reset');
        $observer = new zcObserverLogWriterTextfile($file);
        $observer->updateNotifyAdminFireLogWriterReset();

        $this->assertFileExists($file);

        $var = file($file);
        $this->assertEquals(1, sizeof($var));
        $this->assertNotContains('dummy data', $var[0]); // should not find the dummy data
        $this->assertContains('Log reset by', $var[0]); // should find the reset notice
    }

    public function testFileLogWriterResetViaListener()
    {
        $file = $this->initTestFileSystem(true, 'This is dummy data which should disappear during reset');
        $observer = new zcObserverLogWriterTextfile($file);
        $observer2 = new zcObserverLogEventListener();
        $observer2->updateNotifyAdminActivityLogReset();
        $this->assertFileExists($file);
        $var = file($file);
        $this->assertEquals(1, sizeof($var));
        $this->assertNotContains('dummy data', $var[0]); // should not find the dummy data
        $this->assertContains('Log reset by', $var[0]); // should find the reset notice
    }

    public function testFileLogWriterUpdate()
    {
        $file = $this->initTestFileSystem(true, 'placeholder');
        $data = array('severity' => 'warning', 'ip_address' => 'localhost', 'page_accessed' => 'testLogWriterUpdate');

        $observer = new zcObserverLogWriterTextfile($file);
        $stdClass = new stdClass();
        $observer->updateNotifyAdminFireLogWriters($stdClass, '', $data);
        $this->assertFileExists($file);

        $var = file($file);
        $line = sizeof($var) - 1;

        $this->assertGreaterThan(1, sizeof($var));
        $this->assertNotContains('Log reset', $var[0]); // should not find a reset message

        $this->assertEquals(substr($var[$line], 0, strlen($data['severity'])), 'warning'); // should find the severity level
        $this->assertContains('testLogWriterUpdate', $var[$line]); // should find the message
    }

    public function testHelperFunctionZenRecordAdminActivity()
    {
        global $zco_notifier;
        $zco_notifier = new notifier;
        define('CHARSET', 'utf-8');
        $message = '1abcdefgh';
        $severity = 'critical';

        $file = $this->initTestFileSystem(true, 'placeholder');

        $observer = new zcObserverLogWriterTextfile($file);
        $observer2 = new zcObserverLogEventListener();

        zen_record_admin_activity($message, $severity);

        $this->assertFileExists($file);

        $var = file($file);
        $line = sizeof($var) - 1;

        $this->assertGreaterThan(1, sizeof($var));
        $this->assertEquals(substr($var[$line], 0, strlen($severity)),
            $severity); // test that the specified severity passes through
        $this->assertContains($message, $var[$line]); // should find the message
    }


    /* ********************************************************* */

    /** db writer unit tests (note: other methods are tested using functional db tests **/
    public function testDbLogWriterInstantiation()
    {
        $observer = new zcObserverLogWriterDatabase();
        $this->assertInstanceOf('zcObserverLogWriterDatabase', $observer);
    }

    public function testDbPrepareLogData()
    {
        $specific_message = 'test1\ntest2';
        $severity = 'warning';
        $postdata = json_encode(array('name' => 'x', 'desc' => 'y'));
        $flagged = false;
        $notes = false;

        $log_data = array(
            'event_epoch_time' => time(),
            'admin_id' => 0,
            'page_accessed' => 'testpage',
            'page_parameters' => preg_replace('/(&amp;|&)$/', '', '&amp;item1=abc&amp;item2=defg'),
            'specific_message' => $specific_message,
            'ip_address' => 'localhost',
            'postdata' => $postdata,
            'flagged' => $flagged,
            'attention' => ($notes === false ? '' : $notes),
            'severity' => $severity,
        );

        $observer = new zcObserverLogWriterDatabase();
        $result = $observer->dbPrepareLogData($log_data);

        $this->assertTrue($result[5]['fieldName'] == 'gzpost' && $result[5]['value'] == gzdeflate($postdata, 7));
        $this->assertTrue($result[6]['fieldName'] == 'flagged' && $result[6]['value'] == $flagged);
        $this->assertTrue($result[8]['fieldName'] == 'severity' && $result[8]['value'] == $severity);
        $this->assertTrue($result[9]['fieldName'] == 'logmessage' && $result[9]['value'] == "test1\ntest2"); // note that this test compares using double-quotes, vs the original string which had single quotes
    }
}
