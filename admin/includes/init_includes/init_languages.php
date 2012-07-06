<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_languages.php 18695 2011-05-04 05:24:19Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// set the language
  if (!isset($_SESSION['language']) || isset($_GET['language'])) {

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();

    if (isset($_GET['language']) && zen_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
      $lng->set_language(DEFAULT_LANGUAGE);
    }

    $_SESSION['language'] = (zen_not_null($lng->language['directory']) ? $lng->language['directory'] : 'english');
    $_SESSION['languages_id'] = (zen_not_null($lng->language['id']) ? $lng->language['id'] : 1);
    $_SESSION['languages_code'] = (zen_not_null($lng->language['code']) ? $lng->language['code'] : 'en');
  }

// temporary patch for lang override chicken/egg quirk
  $template_query = $db->Execute("select template_dir from " . TABLE_TEMPLATE_SELECT . " where template_language in (" . (int)$_SESSION['languages_id'] . ', 0' . ") order by template_language DESC");
  $template_dir = $template_query->fields['template_dir'];

// include the language translations
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
  $current_page = basename($PHP_SELF);
  if (file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page)) {
    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page);
  }

  if ($za_dir = @dir(DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions')) {
    while ($zv_file = $za_dir->read()) {
      if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
        require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions/' . $zv_file);
      }
    }
    $za_dir->close();
  }
