<?php
/**
 * @package plugins
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 14:21:34 2012 -0400 New in v1.5.1 $
 *
 * Designed for v1.5.1
 */

class products_viewed_counter extends base {

  function __construct() {
    $this->attach($this, array('NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR'));
  }

  function update(&$class, $eventID, $paramsArray = array())
  {
    if ($eventID == 'NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR')
    {
      if (defined('LEGACY_PRODUCTS_VIEWED_COUNTER') && LEGACY_PRODUCTS_VIEWED_COUNTER == 'on')
      {
        global $db;
        $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
                set        products_viewed = products_viewed+1
                where      products_id = '" . (int)$paramsArray . "'
                and        language_id = '" . (int)$_SESSION['languages_id'] . "'";
        $res = $db->Execute($sql);
      }
    }
  }
}
