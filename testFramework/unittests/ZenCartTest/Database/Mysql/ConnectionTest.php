<?php

namespace ZenCartTest\Database\Mysql;

use PHPUnit_Framework_TestCase as TestCase;

use ZenCart\Database\Mysql\Connection;

class ConnectionTest extends TestCase {

  public static function setUpBeforeClass() {
    include_once DIR_FS_CATALOG . DIR_WS_CLASSES . '/class.base.php';
  }

  /**
   * @group database
   */
  public function testConstruct() {
    try {
      $subject = new Connection;
      $this->assertInstanceOf("ZenCart\Database\ConnectionInterface", $subject);
      $this->assertInstanceOf("base", $subject);
    } catch (\Exception $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testClose() {
    $subject = new Connection;
    $this->assertNull($subject->close());
  }

  /**
   * @group database
   * @depends testClose
   */
  public function testSimpleConnect() {
    $subject = new Connection;
    $this->assertTrue(
      $subject->simpleConnect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME)
    );
    $this->assertTrue($subject->close());
    $this->assertFalse(
      $subject->simpleConnect('host', 'user', 'pass', 'name')
    );
    try {
      var_export($subject->show_error(), 1);
    } catch (\Exception $e) {}
  }

  /**
   * @group database
   * @depends testSimpleConnect
   */
  public function testConnect() {
    $subject = new Connection;
    $this->assertTrue(
      $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME)
    );
    $this->assertTrue($subject->close());
    $this->assertFalse(
      $subject->connect('host', 'user', 'pass', 'name')
    );
  }

  /**
   * @group database
   * @depends testConnect
   */
  public function testSelectDb() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertTrue($subject->selectdb(DB_DBNAME));
    $this->assertFalse($subject->selectdb('name'));
  }

  /**
   * @group database
   * @depends testSelectDb
   */
  public function testPrepareInput() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInternalType('string', $subject->prepare_input('foo'));
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testGetResult() {
    $subject = new Connection;
    $this->assertNull($subject->getResult());
  }

  /**
   * @group database
   * @depends testGetResult
   */
  public function testSetResult() {
    $subject = new Connection;
    $base = $this->getMockForAbstractClass('ZenCart\Cache\CacheInterface');
    $subject->attach($base, array('*'));
    $this->assertSame($subject, $subject->setResult(false, $base));
  }

  /**
   * @group database
   * @depends testSetResult
   */
  public function testSetResultThrowsException() {
    $subject = new Connection;
    $base = $this->getMock("base");
    $this->setExpectedException('InvalidArgumentException');
    $subject->setResult(false, $base);
  }

  /**
   * @group database
   * @depends testPrepareInput
   */
  public function testQuery() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInstanceOf(
      'ZenCart\Database\ResultInterface',
      $subject->query('select * from admin')
    );
    try {
      $this->assertFalse($subject->query("set foo='fail'"));
      $this->assertInternalType('string', $subject->show_error());
    } catch (\Exception $e) {}
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testQueryThrowsException() {
    $subject = new Connection;
    $this->setExpectedException('RuntimeException');
    $subject->query("select * from admin");
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testExecute() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInstanceOf(
      'ZenCart\Database\ResultInterface',
      $subject->Execute('select * from admin')
    );
  }

  /**
   * @group database
   * @depends testExecute
   */
  public function testExecuteRandomMulti() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInstanceOf(
      'ZenCart\Database\ResultInterface',
      $subject->ExecuteRandomMulti('select * from admin')
    );
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testShowError() {
    $subject = new Connection;
    try {
      $subject->connect(DB_HOST, DB_USER, DB_PASS, 'foo');
    } catch (\Exception $e) {
      $this->assertInternalType('string', $subject->show_error());
    }
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testMetaColumns() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInternalType('array', $subject->metaColumns('admin'));
  }

  /**
   * @group database
   * @depends testConnect
   */
  public function testGetServerInfo() {
    $subject = new Connection;
    $this->assertEquals('UNKNOWN', $subject->get_server_info());
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInternalType('string', $subject->get_server_info());
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testQueryCount() {
    $subject = new Connection;
    $this->assertInternalType('integer', $subject->queryCount());
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testQueryTime() {
    $subject = new Connection;
    $this->assertInternalType('integer', $subject->queryTime());
  }

  /**
   * @group database
   * @depends testQuery
   */
  public function testPerform() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertTrue(
      $subject->perform(
        "admin_activity_log",
        array('admin_id' => 1, 'page_accessed' => 'categories.php')
      )
    );
    $this->assertInternalType('integer', $subject->insert_ID());
    $this->assertTrue(
      $subject->perform(
        "admin_activity_log",
        array('flagged' => 1),
        Connection::PERFORM_TYPE_UPDATE,
        'log_id = ' . $subject->insert_ID()
      )
    );
  }

  /**
   * @group database
   * @depends testConnect
   */
  public function testBindVars() {
    $subject = new Connection;
    $subject->connect(DB_HOST, DB_USER, DB_PASS, DB_DBNAME);
    $this->assertInternalType('string', $subject->bindVars(':foo', 'foo', 'bar'));
  }

}