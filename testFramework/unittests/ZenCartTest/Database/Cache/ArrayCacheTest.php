<?php

namespace ZenCartTest\Database\Cache;

use PHPUnit_Framework_TestCase as TestCase;

use ZenCart\Database\Cache\ArrayCache;
use ZenCart\Database\ConnectionInterface;
use ZenCart\Database\Result;

class ArrayCacheTest extends TestCase {

  /**
   * @var ArrayCache
   */
  private $subject;

  public function setUp() {
    $this->subject = new ArrayCache();
  }

  /**
   * @group database
   * @group cache
   */
  public function testUpdateNoParams() {
    $db = $this->getMockForAbstractClass(
      'ZenCart\Database\ConnectionInterface'
    );
    $this->assertNull($this->subject->update($db, 'foo', null));
  }

  /**
   * @group database
   * @group cache
   * @depends testUpdateNoParams
   */
  public function testUpdateBegin() {
    $sql    = 'select * from admin';
    $event  = ConnectionInterface::EVENT_QUERY_BEGIN;
    $params = array('sql' => $sql);
    $result = new Result(array());
    $db     = $this->getMockForAbstractClass(
      'ZenCart\Database\ConnectionInterface'
    );

    $this->subject->store($sql, $result);
    $this->assertEquals($result, $this->subject->read($sql));
    $db->expects($this->once())->method('setResult');

    $this->assertNull($this->subject->update($db, $event, $params));
  }

  /**
   * @group database
   * @group cache
   * @depends testUpdateBegin
   */
  public function testUpdateEnd() {
    $sql    = 'select * from admin';
    $event  = ConnectionInterface::EVENT_QUERY_END;
    $params = array('sql' => $sql);
    $result = new Result(array());
    $db     = $this->getMockForAbstractClass(
      'ZenCart\Database\ConnectionInterface'
    );

    $db->expects($this->once())
       ->method('getResult')
       ->will($this->returnValue($result));

    $this->assertNull($this->subject->update($db, $event, $params));
  }

}
