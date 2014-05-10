<?php
/**
 * zcListingBoxManager
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxManager
 *
 * @package classes
 */
class zcListingBoxManager extends base
{
  /**
   *
   * @var array
   */
  protected $listingBoxesEnabled;
  /**
   *
   * @var array
   */
  protected $listingBoxes;
  /**
   *
   * @var object
   */
  private static $instance = NULL;
  /**
   * getInstance
   *
   * enforces singleton on class
   */
  public static function getInstance($location)
  {
    if (! self::$instance) {
      $class = __CLASS__;
      self::$instance = new $class($location);
    }
    return self::$instance;
  }
  /**
   * constructor
   *
   */
  public function __construct($location)
  {
    global $db;

    $this->listingBoxesEnabled = array();
    $sql = "SELECT * FROM " . TABLE_LISTINGBOXES_TO_LISTINGBOXGROUPS . " as l2c
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS . " as c ON  l2c.group_id = c.group_id
            LEFT JOIN " . TABLE_LISTINGBOXGROUPS_TO_LOCATIONS . " as c2l on c2l.group_id = c.group_id
            where c2l.location_key = :locationKey: ORDER BY c2l.sort_order, l2c.sort_order";
    $sql = $db->bindVars($sql, ':locationKey:', $location, 'string');
    $result = $db->execute($sql);
    while ( ! $result->EOF ) {
      $this->listingBoxesEnabled [$result->fields ['listingbox']] = $result->fields ;
      $result->moveNext();
    }
    $this->notify('NOTIFY_LISTING_BOX_MANAGER_CONSTRUCTOR_END');
  }
  /**
   * build all the enabled listingboxes.
   * only actually push them to the output array if they have some content
   */
  public function buildListingBoxes()
  {
    $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_START');
    foreach ( $this->listingBoxesEnabled as $listingBox => $entry ) {
      require (DIR_WS_MODULES . 'listingboxes/' . "class." . $listingBox . ".php");
      $box = new $listingBox();
      $box->init();
      if ($box->gethasContent()) {
        $this->listingBoxes [] = $box->getTemplateVariables();
      }
    }
    $this->notify('NOTIFY_LISTING_BOX_MANAGER_BUILDLISTINGBOXES_END');
  }
  /**
   * getter
   */
  public function getListingBoxes()
  {
    $this->notify('NOTIFY_LISTING_BOX_MANAGER_GETLISTINGBOXES_START');
    return $this->listingBoxes;
  }
}