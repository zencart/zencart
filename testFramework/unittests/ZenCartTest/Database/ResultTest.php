<?php

namespace ZenCartTest\Database;

use PHPUnit_Framework_TestCase as TestCase;

use ZenCart\Database\Result;

class ResultTest extends TestCase {

  /**
   * @group database
   */
  public function testConstruct() {
    try {
      $this->assertInstanceOf(
        "ZenCart\Database\ResultInterface",
        new Result(array())
      );
    } catch (\Exception $e) {
      $this->fail($e->getMessage());
    }
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testCount() {
    $result = new Result(array(array('id' => 1)));
    $this->assertEquals(1, $result->count());
  }

  /**
   * @group database
   * @depends testCount
   */
  public function testRecordCount() {
    $result = new Result(array());
    $this->assertEquals(0, $result->RecordCount());
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testKey() {
    $result = new Result(array());
    $this->assertEquals(0, $result->key());
  }

  /**
   * @group database
   * @depends testKey
   */
  public function testNext() {
    $result = new Result(array());
    $this->assertNull($result->next());
    $this->assertEquals(1, $result->key());
  }

  /**
   * @group database
   * @depends testNext
   */
  public function testRewind() {
    $result = new Result(array());
    $this->assertNull($result->rewind());
    $this->assertEquals(0, $result->key());
  }

  /**
   * @group database
   * @depends testNext
   */
  public function testValid() {
    $result = new Result(array());
    $this->assertFalse($result->valid());
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testCurrent() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));

    $this->assertEquals(array('id' => 1), $result->current());
  }

  /**
   * @group database
   * @depends testCurrent
   */
  public function testSeek() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));

    $this->assertNull($result->seek(2));
    $this->assertEquals(array('id' => 3), $result->current());
  }

  /**
   * @group database
   * @depends testSeek
   */
  public function testSeekThrowsException() {
    $result = new Result(array());
    $this->setExpectedException('OutOfBoundsException');
    $result->seek(5);
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testToArray() {
    $result = new Result(array());
    $this->assertInternalType('array', $result->toArray());
  }

  /**
   * @group database
   * @depends testConstruct
   */
  public function testSerialize() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));
    $this->assertInternalType('string', serialize($result));
  }

  /**
   * @group database
   * @depends testSerialize
   */
  public function testUnserialize() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));
    $data   = serialize($result);
    $result = unserialize($data);
    $this->assertEquals(array('id' => 1), $result->current());
  }


  /**
   * @group database
   * @depends testSeek
   */
  public function testMoveNext() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));

    $this->assertNull($result->MoveNext());
    $this->assertEquals(array('id' => 2), $result->fields);
  }

  /**
   * @group database
   * @depends testMoveNext
   */
  public function testMove() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));

    $this->assertNull($result->Move(2));
    $this->assertEquals(array('id' => 3), $result->fields);
  }

  /**
   * @group database
   * @depends testMove
   */
  public function testMoveNextRandom() {
    $result = new Result(array(
      array('id' => 1),
      array('id' => 2),
      array('id' => 3),
    ));

    $this->assertNull($result->MoveNextRandom());
    $this->assertTrue(isset($result->fields['id']));
    $result->MoveNextRandom();
    $this->assertTrue(isset($result->fields['id']));
    $result->MoveNextRandom();
    $this->assertNull($result->current());
  }

}
