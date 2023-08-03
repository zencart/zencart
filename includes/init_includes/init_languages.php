<?php

/**
 * initialise language handling
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 24 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!isset($_SESSION['language']) || isset($_GET['language'])) {
  $lng = new language();
  if (!empty($_GET['language'])) {
    $lng->set_language($_GET['language']);
    $zco_notifier->notify('NOTIFY_LANGUAGE_CHANGE_REQUESTED_BY_VISITOR', $_GET['language'], $lng);
  } else {
    if (LANGUAGE_DEFAULT_SELECTOR == 'Browser') {
      $lng->get_browser_language();
      if (empty($lng->language['id'])) {
        $lng->set_language(DEFAULT_LANGUAGE);
      }
    } else {
      $lng->set_language(DEFAULT_LANGUAGE);
    }
  }
  $_SESSION['language'] = (!empty($lng->language['directory']) ? $lng->language['directory'] : 'english');
  $_SESSION['languages_id'] = (!empty($lng->language['id']) ? (int)$lng->language['id'] : 1);
  $_SESSION['languages_code'] = (!empty($lng->language['code']) ? $lng->language['code'] : 'en');
}
