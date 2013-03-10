<?php

namespace ZenCartTest\Database;

use PHPUnit_Framework_TestCase as TestCase;

use ZenCart\Common\ValueObject;

class ValueObjectTest extends TestCase {

  /**
   * @group common
   */
  public function testGet() {
    $subject = new ValueObject(array('foo' => 'bar'));
    $this->assertEquals('bar', $subject->foo);
  }

  /**
   * @group common
   * @depends testGet
   */
  public function testGetThrowsException() {
    $subject = new ValueObject(array());
    $this->setExpectedException('InvalidArgumentException');
    $subject->foo;
  }

  /**
   * @group common
   */
  public function testIsset() {
    $subject = new ValueObject(array('foo' => 'bar'));
    $this->assertTrue(isset($subject->foo));
  }

  /**
   * @group common
   */
  public function testSetThrowsException() {
    $subject = new ValueObject(array());
    $this->setExpectedException('BadMethodCallException');
    $subject->foo = 'bar';
  }

  /**
   * @group common
   */
  public function testUnsetThrowsException() {
    $subject = new ValueObject(array('foo' => 'bar'));
    $this->setExpectedException('BadMethodCallException');
    unset($subject->foo);
  }

}