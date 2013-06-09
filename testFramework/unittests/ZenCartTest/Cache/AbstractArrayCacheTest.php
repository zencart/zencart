<?php

namespace ZenCartTest\Cache;

use PHPUnit_Framework_TestCase as TestCase;

class AbstractArrayCacheTest extends TestCase {

  /**
   * @var ZenCart\Cache\AbstractArrayCache
   */
  protected $cache;

  public function setUp() {
    $this->cache = $this->getMockForAbstractClass(
      'ZenCart\Cache\AbstractArrayCache'
    );
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
  public function testExists() {
    $this->assertFalse($this->cache->exists('foo'));
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
    $this->assertTrue($this->cache->store('foo', array('bar' => 1)));
  }

  /**
   * @group cache
   * @depends testStore
   */
  public function testRead() {
    $this->assertNull($this->cache->read('foo'));
    $this->cache->store('foo', array('bar' => 1));
    $this->assertEquals(array('bar' => 1), $this->cache->read('foo'));
  }

  /**
   * @group cache
   * @depends testRead
   */
  public function testExpire() {
    $this->assertTrue($this->cache->expire('foo'));
  }

  /**
   * @group cache
   * @depends testRead
   */
  public function testFlush() {
    $this->cache->store('foo', array('bar' => 1));
    $this->assertTrue($this->cache->flush());
  }

}
