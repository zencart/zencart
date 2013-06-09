<?php

namespace ZenCartTest\Cache;

use PHPUnit_Framework_TestCase as TestCase;

class AbstractFileCacheTest extends TestCase {

  /**
   * @var ZenCart\Cache\AbstractFileCache
   */
  protected $cache;

  public function setUp() {
    $this->cache = $this->getMockBuilder('ZenCart\Cache\AbstractFileCache')
         ->setConstructorArgs(array('directory' => DIR_FS_CATALOG . '/cache'))
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
  public function testConstructThrowsException() {
    $this->setExpectedException('InvalidArgumentException');
    $this->getMockBuilder('ZenCart\Cache\AbstractFileCache')
         ->setConstructorArgs(array('directory' => null))
         ->getMockForAbstractClass();
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetDirectory() {
    $this->assertInternalType('string', $this->cache->getDirectory());
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetIdPrefix() {
    $this->assertInternalType('string', $this->cache->getIdPrefix());
  }

  /**
   * @group cache
   * @depends testConstruct
   */
  public function testGetFileExtension() {
    $this->assertInternalType('string', $this->cache->getFileExtension());
  }

  /**
   * @group cache
   * @depends testGetIdPrefix
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
