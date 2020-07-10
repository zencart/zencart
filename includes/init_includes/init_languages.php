<?php

/**
 * initialise language handling
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2019 Oct 12 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!isset($_SESSION['language']) || isset($_GET['language'])) {
  $lng = new language();
  if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
    $lng->set_language($_GET['language']);
    $zco_notifier->notify('NOTIFY_LANGUAGE_CHANGE_REQUESTED_BY_VISITOR', $_GET['language'], $lng);
  } else {
    if (LANGUAGE_DEFAULT_SELECTOR == 'Browser') {
      $lng->get_browser_language();
      if (!zen_not_null($lng->language['id'])) {
        $lng->set_language(DEFAULT_LANGUAGE);
      }
    } else {
      $lng->set_language(DEFAULT_LANGUAGE);
    }
  }
  $_SESSION['language'] = (zen_not_null($lng->language['directory']) ? $lng->language['directory'] : 'english');
  $_SESSION['languages_id'] = (zen_not_null($lng->language['id']) ? (int)$lng->language['id'] : 1);
  $_SESSION['languages_code'] = (zen_not_null($lng->language['code']) ? $lng->language['code'] : 'en');
}
