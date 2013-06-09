<?php

namespace ZenCartTest\Cache;

use PHPUnit_Framework_TestCase as TestCase;

use ZenCart\Database\Result;

class AbstractDatabaseCacheTest extends TestCase {

  /**
   * @var ZenCart\Cache\AbstractDatabaseCache
   */
  protected $cache;

  /**
   * @var ZenCart\Database\ConnectionInterface
   */
  protected $connection;

  public function setUp() {
    $this->connection = $this->getMockForAbstractClass(
      'ZenCart\Database\ConnectionInterface'
    );

    $this->cache = $this->getMockBuilder('ZenCart\Cache\AbstractDatabaseCache')
         ->setConstructorArgs(array('connection' => $this->connection))
         ->getMockForAbstractClass();
  }

  /**
   * @group cache
   */
  public function testConstruct() {
    $this->assertInstanceOf('ZenCart\Cache\CacheInterface', $this->cache);
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetConnection() {
    $this->assertSame($this->connection, $this->cache->getConnection());
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetTable() {
    $this->assertInternalType('string', $this->cache->getTable());
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetFilterField() {
    $this->assertInternalType('string', $this->cache->getFilterField());
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetDataField() {
    $this->assertInternalType('string', $this->cache->getDataField());
  }

  /**
   * @group cache
   * @depends testGetDataField
   */
  public function testExists() {
    $this->connection
      ->expects($this->any())
      ->method('Execute')
      ->will($this->returnValue(true));
    $this->assertTrue($this->cache->exists('foo'));
  }

  /**
   * @group cache
   * @depends testExists
   */
  public function testIsExpired() {
    $this->assertTrue($this->cache->isExpired('foo'));
  }

  /**
   * @group cache
   * @depends testIsExpired
   */
  public function testStore() {
    $this->connection
      ->expects($this->any())
      ->method('perform')
      ->will($this->returnValue(true));
    $this->assertTrue($this->cache->store('foo', array('bar' => 1)));
  }

  /**
   * @group cache
   * @depends testStore
   */
  public function testRead() {
    $data = array('bar' => 1);
    $result = new Result(
      array(array($this->cache->getDataField() => serialize($data)))
    );
    $this->connection
      ->expects($this->any())
      ->method('Execute')
      ->will($this->returnValue($result));
    $this->assertEquals($data, $this->cache->read('foo'));
  }

  /**
   * @group cache
   * @depends testRead
   */
  public function testExpire() {
    $this->connection
      ->expects($this->any())
      ->method('Execute')
      ->will($this->returnValue(true));
    $this->assertTrue($this->cache->expire('foo'));
  }

  /**
   * @group cache
   * @depends testRead
   */
  public function testFlush() {
    $this->connection
      ->expects($this->any())
      ->method('Execute')
      ->with("DELETE FROM " . $this->cache->getTable())
      ->will($this->returnValue(true));
    $this->assertTrue($this->cache->flush());
  }

}
