<?php
/**
 * File contains just the base class
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: class.base.php 14535 2009-10-07 22:16:19Z wilt $
 */
/**
 * abstract class base
 *
 * any class that wants to notify or listen for events must extend this base class
 *
 * @package classes
 */
class base {
  /**
   * method used to an attach an observer to the notifier object
   *
   * NB. We have to get a little sneaky here to stop session based classes adding events ad infinitum
   * To do this we first concatenate the class name with the event id, as a class is only ever going to attach to an
   * event id once, this provides a unigue key. To ensure there are no naming problems with the array key, we md5 the unique
   * name to provide a unique hashed key.
   *
   * @param object Reference to the observer class
   * @param array An array of eventId's to observe
   */
  function attach(&$observer, $eventIDArray) {
    foreach($eventIDArray as $eventID) {
      $nameHash = md5(get_class($observer).$eventID);
      base::setStaticObserver($nameHash, array('obs'=>&$observer, 'eventID'=>$eventID));
    }
  }
  /**
   * method used to detach an observer from the notifier object
   * @param object
   * @param array
   */
  function detach($observer, $eventIDArray) {
    foreach($eventIDArray as $eventID) {
      $nameHash = md5(get_class($observer).$eventID);
      base::unsetStaticObserver($nameHash);
    }
  }
  /**
   * method to notify observers that an event has occurred in the notifier object
   *
   * @param string The event ID to notify for
   * @param array paramters to pass to the observer, useful for passing stuff which is outside of the 'scope' of the observed class.
   */
  function notify($eventID, $param1 = array(), $param2 = NULL, $param3 = NULL, $param4 = NULL, $param5 = NULL, $param6 = NULL, $param7 = NULL) {
    // notifier trace logging - for advanced debugging purposes only --- NOTE: This log file can get VERY big VERY quickly!
    if (defined('NOTIFIER_TRACE') && NOTIFIER_TRACE != '' && NOTIFIER_TRACE !== 0 && NOTIFIER_TRACE != FALSE && NOTIFIER_TRACE != 'false') {
      $file = DIR_FS_LOGS . '/notifier_trace.log';
      $paramArray = array_merge((array)$param1,(array)$param2,(array)$param3,(array)$param4,(array)$param5,(array)$param6,(array)$param7);
      if (NOTIFIER_TRACE == 'var_export' || NOTIFIER_TRACE == 'var_dump' || NOTIFIER_TRACE == 'true') {
        error_log( strftime("%Y-%m-%d %H:%M:%S") . ' [main_page=' . $_GET['main_page'] . '] ' . $eventID . ((count($paramArray) == 0) ? '' : ', ' . var_export($paramArray, true)) . "\n", 3, $file);
      } elseif (NOTIFIER_TRACE == 'print_r') {
        error_log( strftime("%Y-%m-%d %H:%M:%S") . ' [main_page=' . $_GET['main_page'] . '] ' . $eventID . ((count($paramArray) == 0) ? '' : ', ' . print_r($paramArray, true)) . "\n", 3, $file);
      }
    }

    // handle observers
    $observers = & base::getStaticObserver();
    if (is_null($observers)) {
      return;
    } else
    {
      foreach($observers as $key=>$obs) {
        if ($obs['eventID'] == $eventID || $obs['eventID'] === '*') {
         $method = 'update';
         $testMethod = $method . self::camelize(strtolower($eventID), TRUE);
         if (method_exists($obs['obs'], $testMethod))
           $method = $testMethod;
         $obs['obs']->{$method}($this, $eventID, $param1,$param2,$param3,$param4,$param5,$param6,$param7);
        }
      }
    }
  }
  function & getStaticProperty($var)
  {
    static $staticProperty;
    return $staticProperty;
  }
  function & getStaticObserver() {
    return base::getStaticProperty('observer');
  }
  function setStaticObserver($element, $value)
  {
    $observer =  & base::getStaticObserver();
    $observer[$element] = $value;
  }
  function unsetStaticObserver($element)
  {
    $observer =  & base::getStaticObserver();
    unset($observer[$element]);
  }
  public static function camelize($rawName, $camelFirst = FALSE)
  {
    if ($rawName == "")
      return $rawName;
    if ($camelFirst)
    {
      $rawName[0] = strtoupper($rawName[0]);
    }
    return preg_replace('/[_-]([0-9,a-z])/e', "strtoupper('\\1')", $rawName);
  }
}