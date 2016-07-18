<?php
/**
 * language Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
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
  protected $available_languages = array('en'=> array('id' => 1, 'name' => 'english', 'image' => 'icon.gif', 'code' => 'en', 'directory' => 'english'));
  /**
   * @var string comma-delimited list of languages supported by the user's browser
   */
  protected $browser_languages = '';
  /**
   * @var string The currently selected language (which is separately set into a session var in init_languages)
   */
  protected $language = '';

  /**
   * @deprecated since v1.6.0
   * @var array DEPRECATED - This is old, and only present for compatibility reasons. Use get_available_languages() instead.
   */
  public $catalog_languages = array();

  /**
   * @param string $lng The language we'd like to set the site to, as long as it exists in the db
   * @return string
   */
  public function __construct($lng = '') {
    global $db;
    $languages_query = "select languages_id, name, code, image, directory
                          from " . TABLE_LANGUAGES . "
                          order by sort_order";
    $result = $db->Execute($languages_query);

    foreach ($result as $val) {
      $this->available_languages[$val['code']] = array(
              'id' => $val['languages_id'],
              'name' => $val['name'],
              'image' => $val['image'],
              'code' => $val['code'],
              'directory' => $val['directory'],
              );
    }

    // for legacy compatibility only:
    $this->catalog_languages = $this->get_available_languages();

    return $this->set_language($lng);
  }

  /**
   * @param string $language The language we want to set the site to, as long as it exists in the db
   * @return array
   */
  public function set_language($language = DEFAULT_LANGUAGE) {
    if (empty($language)) $language = DEFAULT_LANGUAGE;

    if (isset($this->available_languages[$language])) {
      $this->language = $this->available_languages[$language];
    }
    return $this->language;
  }

  /**
   * Returns array of languages installed and configured in the site
   * @return array
   */
  public function get_available_languages()
  {
    return array_values($this->available_languages);
  }

  /**
   * Lookup language details by language ID number
   * (mainly used in admin for displaying language-icons on attribute option-name pages
   * @param integer $lang_id
   * @return array|boolean
   */
  public function get_language_data_by_id($lang_id = 0) {
    if ($lang_id == 0) return false;

    foreach ($this->available_languages as $code => $val) {
      if ($val['id'] == (int)$lang_id) {
        return $val;
      }
    }
  }

  /**
   * Lookup language details by 2-letter language Code
   * @param string $lang_code
   * @return array|boolean
   */
  public function get_language_data_by_code($lang_code = '') {
    if ($lang_code == '') return false;

    foreach ($this->available_languages as $code => $val) {
      if ($val['code'] == $lang_code) {
        return $val;
      }
    }
  }


  /**
   * Determine languages supported by the browser, and set the site to use a corresponding language
   * Matching is attempted according to the order of browser preference, as long as the store supports at least one. Else it aborts.
   * @return array|boolean
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
        if (isset($this->available_languages[$code])) {
          return $this->set_language($code);
        }
      }
    }
  }
}
