<?php
/**
 * compatibility functions - these are things that are being retired in future versions
 * It is better to use the "new way" to do these things ... which is generally demonstrated by the code inside the functions herein.
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: compatibility.php $
 */



/**
 * Lookup Languages Icon
 */
  function zen_get_language_icon($lookup) {
    global $lng;
    $data = $lng->get_language_data_by_id($lookup);
    if ($data == false || $data['image'] == '') return '';
    $icon = zen_image(DIR_WS_CATALOG_LANGUAGES . $data['directory'] . '/images/' . $data['image']);
    return $icon;
  }

/**
 * lookup language dir from id
 */
  function zen_get_language_name($lookup) {
    global $lng;
    $data = $lng->get_language_data_by_id($lookup);
    if ($data == false || $data['image'] == '') return '';
    return $data['directory'];
  }

