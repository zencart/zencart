<?php
/**
 * Test Library
 *
 * @package   tests
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   $Id$
 */

include_once DIR_WS_CLASSES . "class.base.php";
include_once __DIR__ . "/../TestAssets/Observer.php";

/**
 * base test
 */
class BaseTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var base
   */
  protected $base;

  public function setUp()
  {
    $this->base = new base;
  }

  /**
   * @group classes
   */
  public function testGetStaticProperty()
  {
    $this->assertNull($this->base->getStaticProperty("foo"));
  }

  /**
   * @group classes
   * @depends testGetStaticProperty
   */
  public function testGetStaticObserver()
  {
    $observer = & $this->base->getStaticObserver();
    $this->assertNull($observer);
  }

  /**
   * @group classes
   * @depends testGetStaticObserver
   * @runInSeparateProcess
   */
  public function testSetStaticObserver()
  {
    $this->base->setStaticObserver("foo", "bar");
    $observer = & $this->base->getStaticObserver();
    $this->assertInternalType("array", $observer);
    $this->assertArrayHasKey("foo", $observer);
    $this->assertEquals("bar", $observer["foo"]);
  }

  /**
   * @group classes
   * @depends testSetStaticObserver
   * @runInSeparateProcess
   */
  public function testUnsetStaticObserver()
  {
    $this->base->setStaticObserver("foo", "bar");
    $this->base->unsetStaticObserver("foo");
    $this->assertEmpty($this->base->getStaticObserver());
  }

  /**
   * @group classes
   * @depends testSetStaticObserver
   * @runInSeparateProcess
   */
  public function testAttach()
  {
    $observer = new stdClass;
    $eventIDArray = array("foo");
    $this->base->attach($observer, $eventIDArray);
    $observers = & $this->base->getStaticObserver();
    $this->assertNotEmpty($observers);
    $attached = array_shift($observers);
    $this->assertInternalType("array", $attached);
    $this->assertArrayHasKey("obs", $attached);
    $this->assertEquals($observer, $attached["obs"]);
    $this->assertArrayHasKey("eventID", $attached);
    $this->assertEquals($eventIDArray[0], $attached["eventID"]);
  }

  /**
   * @group classes
   * @depends testAttach
   * @depends testUnsetStaticObserver
   * @runInSeparateProcess
   */
  public function testDetach()
  {
    $observer = new stdClass;
    $eventIDArray = array("foo", "bar");
    $this->base->attach($observer, $eventIDArray);
    $observers = & $this->base->getStaticObserver();
    $this->assertCount(2, $observers);
    $this->base->detach($observer, array("foo"));
    $this->assertCount(1, $observers);
  }

  /**
   * @group classes
   */
  public function testCamelizeWithEmptyString()
  {
    $this->assertEquals("", base::camelize(""));
  }

  /**
   * @group classes
   * @dataProvider getRawNames
   */
  public function testCamelize($rawName, $expected)
  {
    $this->assertEquals($expected, base::camelize($rawName));
  }

  public function getRawNames()
  {
    return array(
      array("foo_bar", "fooBar"),
      array("foo-bar", "fooBar"),
      array("foo_bAR", "fooBAR"),
    );
  }

  /**
   * @group classes
   * @depends testCamelize
   */
  public function testCamelizeCamelFirst()
  {
    $this->assertEquals("FooBar", base::camelize("foo_bar", TRUE));
  }

  /**
   * @group classes
   * @depends testDetach
   * @runInSeparateProcess
   */
  public function testNotify()
  {
    $observer = $this->getMock("Observer");
    $observer->expects($this->once())
      ->method("update")
      ->with($this->base, "bar");

    $this->base->attach($observer, array("*"));
    $this->base->notify("bar");
  }

  /**
   * @group classes
   * @depends testNotify
   * @runInSeparateProcess
   */
  public function testNotifyNoObservers()
  {
    $this->base->notify("bar");
    $this->assertTrue(TRUE);
  }

  /**
   * @group classes
   * @depends testNotify
   * @runInSeparateProcess
   */
  public function testNotifyVarExport()
  {
    define("NOTIFIER_TRACE", "var_dump");
    defined("DIR_FS_LOGS") || define("DIR_FS_LOGS", CWD . "/logs");
    $_GET["main_page"] = "test";
    try {
      $this->base->notify("foo", "log me!", __CLASS__, __FUNCTION__, __LINE__);
    } catch (Exception $e) {
      $this->assertTrue(TRUE);
    }
  }

  /**
   * @group classes
   * @depends testNotifyVarExport
   * @runInSeparateProcess
   */
  public function testNotifyPrintR()
  {
    define("NOTIFIER_TRACE", "print_r");
    defined("DIR_FS_LOGS") || define("DIR_FS_LOGS", CWD . "/logs");
    $_GET["main_page"] = "test";
    try {
      $this->base->notify("foo", "log me!", __CLASS__, __FUNCTION__, __LINE__);
    } catch (Exception $e) {
      $this->assertTrue(TRUE);
    }
  }

}
