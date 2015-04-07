<?php
/**
 * language Class.
 *
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: language.php drbyte  Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * language Class.
 * Class to handle language settings for customer viewing
 *
 * @package classes
 */
class language extends base {
  /**
   * @var Array of all available languages in the store
   */
  public $catalog_languages = array();
  /**
   * @var string comma-delimited list of languages supported by the user's browser
   */
  protected $browser_languages = '';
  /**
   * @var string The currently selected language (which is separately set into a session var in init_languages)
   */
  public $language = '';

  /**
   * @param string $lng The language we'd like to set the site to, as long as it exists in the db
   */
  public function __construct($lng = '') {
    global $db;

    $languages_query = "select languages_id, name, code, image, directory
                          from " . TABLE_LANGUAGES . "
                          order by sort_order";
    $result = $db->Execute($languages_query);

    foreach ($result as $val) {
      $this->catalog_languages[$val['code']] = array(
              'id' => $val['languages_id'],
              'name' => $val['name'],
              'image' => $val['image'],
              'code' => $val['code'],
              'directory' => $val['directory'],
              );
    }

    $this->set_language($lng);
  }

  /**
   * @param string $language The language we want to set the site to, as long as it exists in the db
   */
  public function set_language($language = DEFAULT_LANGUAGE) {
    if (empty($language)) $language = DEFAULT_LANGUAGE;

    if (isset($this->catalog_languages[$language])) {
      $this->language = $this->catalog_languages[$language];
      return true;
    }
    return false;
  }

  /**
   * Determine languages supported by the browser, and set the site to use a corresponding language
   * Matching is attempted according to the order of browser preference, as long as the store supports at least one. Else it aborts.
   */
  public function get_browser_language() {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $this->browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      for ($i=0, $n=sizeof($this->browser_languages); $i<$n; $i++) {
        $lang = explode(';', $this->browser_languages[$i]);
        if (strlen($lang[0]) == 2) {
          $code = $lang[0];
        } elseif (strpos($lang[0], '-') == 2 || strpos($lang[0], '_') == 2) {
          $code = substr($lang[0], 0, 2);
        } else {
          continue;
        }
        if ($this->set_language($code)) {
          return true;
        }
      }
    }
  }
}
