<?php
/**
 * initialise language handling
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_languages.php 2753 2005-12-31 19:17:17Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!isset($_SESSION['language']) || isset($_GET['language'])) {
  $lng = new language();
  if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
    $val = $lng->set_language($_GET['language']);
  } else {
    if (LANGUAGE_DEFAULT_SELECTOR=='Browser') {
      $val = $lng->get_browser_language();
    } else {
      $val = $lng->set_language(DEFAULT_LANGUAGE);
    }
  }
  $_SESSION['language'] = $val['directory'];
  $_SESSION['languages_id'] = $val['id'];
  $_SESSION['languages_code'] = $val['code'];
}
