<?php
/**
 * File contains just the request class
 *
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class request
 *
 * @package classes
 * @see https://confluence.zen-cart.com/display/PUBDOC/Request+Class
 */
class zcRequest extends base
{
  /**
   *
   * @var $sanitizers array
   */
  protected $sanitizers;
  /**
   *
   * @var $parameterBag array
   */
  protected $parameterBag = array();
  /**
   *
   * @var $rawParameterBag array
   */
  protected $rawParameterBag = array();
  /**
   *
   * @var $instance object
   */
  protected static $instance = null;
  /**
   *
   * @var $context string
   */
  protected $context;
  /**
   *
   * @var $debug boolean
   */
  protected $debug;
  /**
   *
   * @return $instance
   */
  public static function getInstance()
  {
    if (! self::$instance) {
      $class = __CLASS__;
      self::$instance = new $class();
    }
    return self::$instance;
  }
  /**
   *
   * @param string $debug
   */
  public static function init($debug = false)
  {
    global $systemContext;
    if ($debug)
      error_log("zcRequest::init");
    $class = self::getInstance();
    $class->context = $systemContext;
    $class->notify('NOTIFY_REQUEST_SET_CONTEXT');
    $class->debug = $debug;
    $class->initRawParameterBag();
    $class->buildDefaultSanitizers();
    $class->initParameterBag();
    if (! defined('REQUEST_LEGACY_SUPPORT') || (defined('REQUEST_LEGACY_SUPPORT') && REQUEST_LEGACY_SUPPORT == true)) {
      if ($debug)
        error_log('zcRequest legacy support enabled');
      // $class->legacySupport();
    } else {
      if ($debug)
        error_log('zcRequest legacy support not enabled');
      unset($_POST);
      unset($_GET);
      unset($_REQUEST);
    }
  }
  /**
   */
  public static function getRawParameterBag()
  {
    return self::getInstance()->rawParameterBag;
  }
  /**
   */
  public static function getParameterBag()
  {
    return self::getInstance()->parameterBag;
  }
  /**
   *
   * @param unknown $paramName
   * @param string $paramDefault
   * @param string $source
   * @throws Exception
   * @return string
   */
  public static function get($paramName, $paramDefault = NULL, $source = 'get')
  {
    $class = self::getInstance();

    if (isset($class->parameterBag [$source] [$paramName])) {
      return $class->parameterBag [$source] [$paramName];
    } elseif (isset($paramDefault)) {
      return $paramDefault;
    } elseif ($paramName == 'cmd' && IS_ADMIN_FLAG == 'true' && (! defined('REQUEST_LEGACY_SUPPORT') || (defined('REQUEST_LEGACY_SUPPORT') && REQUEST_LEGACY_SUPPORT == true))) {
      return (str_replace('.php', '', basename($_SERVER ['SCRIPT_FILENAME'])));
    } else {
      throw new Exception('Exception: Could not zcRequest::get paramName = ' . $paramName);
    }
  }
  /**
   *
   * @param string $paramName
   * @param string $paramDefault
   * @return Ambigous <string, string>
   */
  public static function readGet($paramName, $paramDefault = NULL)
  {
    return self::get($paramName, $paramDefault, 'get');
  }
  /**
   *
   * @param string $paramName
   * @param string $paramDefault
   * @return Ambigous <string, string>
   */
  public static function readPost($paramName, $paramDefault = NULL)
  {
    return self::get($paramName, $paramDefault, 'post');
  }
  /**
   *
   * @param unknown $paramName
   * @param string $paramDefault
   * @param string $source
   * @throws Exception
   * @return string
   */
  public static function getRaw($paramName, $paramDefault = NULL, $source = 'get')
  {
    $class = self::getInstance();
    if (isset($class->rawParameterBag [$source] [$paramName])) {
      return $class->rawParameterBag ['get'] [$paramName];
    } elseif (isset($paramDefault)) {
      return $paramDefault;
    } else {
      throw new Exception('Exception: Could not request::getRaw ' . $paramName);
    }
  }
  /**
   *
   * @param unknown $paramName
   * @param string $source
   * @return boolean
   */
  public static function has($paramName, $source = 'get')
  {
    $class = self::getInstance();
    if ($source == 'get')
      return (isset($class->parameterBag ['get'] [$paramName])) ? true : false;
    if ($source == 'post')
      return (isset($class->parameterBag ['post'] [$paramName])) ? true : false;
    return false;
  }
  /**
   *
   * @param string $paramName
   * @return boolean
   */
  public static function hasGet($paramName)
  {
    return self::has($paramName, 'get');
  }
  /**
   *
   * @param string $paramName
   * @return boolean
   */
  public static function hasPost($paramName)
  {
    return self::has($paramName, 'post');
  }
  /**
   *
   * @param string $source
   * @return multitype:
   */
  public static function all($source = 'all')
  {
    $class = self::getInstance();
    if ($source == 'get') {
      return isset($class->parameterBag ['get']) ? $class->parameterBag ['get'] : array();
    } elseif ($source == 'post') {
      return isset($class->parameterBag ['post']) ? $class->parameterBag ['post'] : array();
    } else {
      $getArray = isset($class->parameterBag ['get']) ? $class->parameterBag ['get'] : array();
      $postArray = isset($class->parameterBag ['post']) ? $class->parameterBag ['post'] : array();
      $result = array_merge($getArray, $postArray);
      return $result;
    }
  }
  /**
   *
   * @param string $key
   * @param mixed $value
   * @param string $type
   */
  public static function set($key, $value, $type = 'get')
  {
    $class = self::getInstance();
    if ($type == 'get' || $type == 'all') {
      $class->rawParameterBag ['get'] [$key] = $value;
      $class->parameterBag ['get'] [$key] = $value;
    }
    if ($type == 'post' || $type == 'all') {
      $class->rawParameterBag ['post'] [$key] = $value;
      $class->parameterBag ['post'] [$key] = $value;
    }
  }
  /**
   *
   * @param unknown $context
   * @param unknown $entryKey
   * @param unknown $entryParams
   */
  public static function registerEntry($context, $entryKey, $entryParams)
  {
    $class = self::getInstance();
    $class->sanitizers [$context] ['entries'] [$entryKey] [] = $entryParams;
  }
  /**
   *
   * @param string $context
   * @param string $groupKey
   * @param array $groupParams
   */
  public static function registerGroup($context, $groupKey, $groupParams)
  {
    $class = self::getInstance();
    $class->sanitizers [$context] ['groups'] [$groupKey] = $groupParams;
  }
  /**
   *
   * @param string $entryKey
   * @param array $entryParams
   * @param boolean $raw
   */
  public static function applyEntrySanitizer($entryKey, $entryParams, $raw = true)
  {
    $class = self::getInstance();
    $value = self::getRaw($entryKey, null, $entryParams ['source']);
    $type = $entryParams ['source'];
    $class->processParameterBagEntry($type, $entryKey, $value, $entryParams);
  }
  /**
   *
   * @param array $groupList
   * @param array $entryParams
   * @param boolean $raw
   */
  public static function applyGroupSanitizer($groupList, $entryParams, $raw = true)
  {
    $class = self::getInstance();
    foreach ( $groupList as $groupEntry ) {
      $value = self::getRaw($groupEntry, null, $entryParams ['source']);
      $type = $entryParams ['source'];
      $class->processParameterBagEntry($type, $groupEntry, $value, $entryParams);
    }
  }
  /**
   *
   * @param string $debug
   */
  public function setDebug($debug = false)
  {
    $class = self::getInstance();
    $class->debug = $debug;
  }
  /**
   */
  protected function initRawParameterBag()
  {
    $this->rawParameterBag ['post'] = array();
    $this->rawParameterBag ['get'] = array();
    foreach ( $_POST as $key => $value ) {
      $this->rawParameterBag ['post'] [$key] = $value;
    }
    foreach ( $_GET as $key => $value ) {
      $this->rawParameterBag ['get'] [$key] = $value;
    }
  }
  /**
   */
  protected function initParameterBag()
  {
    $this->parameterBag ['post'] = array();
    $this->parameterBag ['get'] = array();
    $this->processParameterBag('post');
    $this->processParameterBag('get');
  }
  /**
   *
   * @param string $type
   */
  protected function processParameterBag($type = 'get')
  {
    if ($this->debug)
      error_log('zcRequest processing parameter bag for ' . $type);
    if (count($this->rawParameterBag) > 0 && isset($this->rawParameterBag [$type])) {
      if (isset($this->sanitizers [$this->context] ['groups'])) {
        $this->processGroupSanitizers($this->context);
      } elseif (isset($this->sanitizers ['all'] ['groups'])) {
        $this->processGroupSanitizers('all');
      }
      foreach ( $this->rawParameterBag [$type] as $key => $value ) {
        if (isset($this->sanitizers [$this->context] ['entries'] [$key])) {
          $this->processParameterBagValue($type, $key, $value, $this->sanitizers [$this->context] ['entries'] [$key]);
        } elseif (isset($this->sanitizers ['all'] ['entries'] [$key])) {
          $this->processParameterBagValue($type, $key, $value, $this->sanitizers ['all'] ['entries'] [$key]);
        } elseif (isset($this->sanitizers [$this->context] ['default'])) {
          $this->processParameterBagValue($type, $key, $value, $this->sanitizers [$this->context] ['default']);
        } elseif (isset($this->sanitizers ['all'] ['default'])) {
          $this->processParameterBagValue($type, $key, $value, $this->sanitizers ['all'] ['default']);
        }
      }
    }
  }
  /**
   *
   * @param sting $type
   * @param string $key
   * @param mixed $value
   * @param array $sanitizer
   */
  protected function processParameterBagValue($type, $key, $value, $sanitizers)
  {
    foreach ( $sanitizers as $sanitizer ) {
      $source = ($sanitizer ['source'] == 'all') ? 'all' : ($sanitizer ['source'] == $type) ? $type : NULL;
      if (isset($source)) {
        $this->processParameterBagEntry($type, $key, $value, $sanitizer);
      }
    }
  }
  /**
   *
   * @param unknown $type
   * @param unknown $key
   * @param unknown $value
   * @param unknown $sanitizer
   */
  protected function processParameterBagEntry($type, $key, $value, $sanitizer)
  {
    if (is_array($value)) {
      $this->processParameterBagValueArray($type, $key, $value, $sanitizer);
    } else {
      $value = $this->sanitizeParameterBagEntry($type, $value, $sanitizer);
      $this->parameterBag [$type] [$key] = $value;
      unset($GLOBALS [$key]);
    }
  }

  /**
   *
   * @param string $type
   * @param string $key
   * @param unknown $value
   * @param array $sanitizer
   */
  protected function processParameterBagValueArray($type, $key, $value, $sanitizer)
  {
    foreach ( $value as $arrayKey => $arrayValue ) {
      $value [$arrayKey] = $this->sanitizeParameterBagEntry($type, $arrayValue, $sanitizer);
    }
    $this->parameterBag [$type] [$key] = $value;
    unset($GLOBALS [$key]);
  }
  /**
   *
   * @param unknown $type
   * @param unknown $value
   * @param unknown $sanitizer
   * @return mixed
   */
  protected function sanitizeParameterBagEntry($type, $value, $sanitizer)
  {
    $parameters = isset($sanitizer ['parameters']) ? $sanitizer ['parameters'] : array();
    if (isset($sanitizer ['type']) && is_string($sanitizer ['type'])) {
      if (strpos($sanitizer ['type'], '::') !== false) {
        list($class, $method) = preg_split('/::/', $sanitizer ['type']);
        if (method_exists($class, $method)) {
          $value = call_user_func($sanitizer ['type'], $value, $parameters);
          return $value;
        }
      } else {
        $method = 'sanitizer' . ucfirst($sanitizer ['type']);
        if (method_exists($this, $method)) {
          $value = $this->{$method}($value, $parameters);
          return $value;
        }
      }
    }
    if (isset($sanitizer ['type']) && ! is_string($sanitizer ['type']) && is_callable($sanitizer ['type'])) {
      $value = call_user_func($sanitizer ['type'], $value, $parameters);
      return $value;
    }
  }

  /**
   *
   * @param string $context
   */
  protected function processGroupSanitizers($context)
  {
    foreach ( $this->sanitizers [$context] ['groups'] as $groupEntries ) {
      if ($this->debug)
        error_log('zcRequest processing Group sanitizers for ' . $context . ' and type = ' . $groupEntries ['type'] . ' and source = ' . $groupEntries ['source']);
      $type = $groupEntries ['type'];
      $source = $groupEntries ['source'];
      $parameters = (isset($groupEntries ['parameters'])) ? $groupEntries ['parameters'] : '';
      foreach ( $groupEntries ['entries'] as $paramKey ) {
        if ($this->debug)
          error_log('zcRequest processing Group sanitizers for group entry = ' . $paramKey);
        $this->sanitizers [$context] ['entries'] [$paramKey] [] = array(
            'source' => $source,
            'type' => $type,
            'parameters' => $parameters
        );
      }
    }
  }
  /**
   *
   * @param mixed $value
   * @param string $sanitizer
   * @return mixed
   */
  protected function sanitizerRegexFilter($value, $parameters)
  {
    $result = preg_replace($parameters ['regex'], '', $value);
    return $result;
  }
  /**
   *
   * @param unknown $value
   * @param unknown $sanitizer
   * @return unknown
   */
  protected function sanitizerPassthru($value, $parameters)
  {
    return $value;
  }
  /**
   */
  protected function buildDefaultSanitizers()
  {
    $this->sanitizers = array();
    $this->sanitizers ['admin'] ['default'] [] = array(
        'source' => 'get',
        'type' => 'passthru'
    );
    $this->sanitizers ['admin'] ['default'] [] = array(
        'source' => 'post',
        'type' => 'passthru'
    );
    $this->sanitizers ['store'] ['default'] [] = array(
        'source' => 'post',
        'type' => 'passthru'
    );
    $this->sanitizers ['store'] ['default'] [] = array(
        'source' => 'get',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[<>\']/'
        )
    );
    $this->sanitizers ['store'] ['entries'] ['keyword'] [] = array(
        'source' => 'get',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[<>]/'
        )
    );
    $storeGetSaniGroup1 = array(
        'action',
        'addr',
        'alpha_filter_id',
        'alpha_filter',
        'authcapt',
        'chapter',
        'cID',
        'currency',
        'debug',
        'delete',
        'dfrom',
        'disp_order',
        'dto',
        'edit',
        'faq_item',
        'filter_id',
        'goback',
        'goto',
        'gv_no',
        'id',
        'inc_subcat',
        'language',
        'markflow',
        'piece_style_id',
        'nocache',
        'notify',
        'number_of_uploads',
        'order_id',
        'order',
        'override',
        'page',
        'pfrom',
        'pid',
        'pID',
        'pos',
        'product_id',
        'img',
        'products_tax_class_id',
        'pto',
        'agency_id',
        'referer',
        'reviews_id',
        'search_in_description',
        'set_session_login',
        'token',
        'tx',
        'type',
        'zenid',
        'typefilter',
        'main_page',
        'sort',
        'products_id',
        'manufacturers_id',
        'categories_id',
        'cPath'
    );
    self::registerGroup('store', 'storeGetSaniGroup1', array(
        'entries' => $storeGetSaniGroup1,
        'source' => 'all',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[^\/0-9a-zA-Z_:@.-]/'
        )
    ));
    $adminGetSaniGroup1 = array(
        'action',
        'add_products_id',
        'attribute_id',
        'attribute_page',
        'attributes_id',
        'banner',
        'bID',
        'box_name',
        'build_cat',
        'came_from',
        'categories_update_id',
        'cID',
        'cid',
        'configuration_key_lookup',
        'copy_attributes',
        'cpage',
        'cPath',
        'current_category_id',
        'current',
        'customer',
        'debug',
        'debug2',
        'debug3',
        'define_it',
        'download_reset_off',
        'download_reset_on',
        'end_date',
        'ezID',
        'fID',
        'filename',
        'flag',
        'flagbanners_on_ssl',
        'flagbanners_open_new_windows',
        'gID',
        'gid',
        'global',
        'go_back',
        'id',
        'info',
        'ipnID',
        'keepslashes',
        'layout_box_name',
        'lID',
        'list_order',
        'language',
        'lng_id',
        'lngdir',
        'mail_sent_to',
        'manual',
        'master_category',
        'mID',
        'mode',
        'module',
        'month',
        'na',
        'nID',
        'nogrants',
        'ns',
        'number_of_uploads',
        'oID',
        'oldaction',
        'option_id',
        'option_order_by',
        'option_page',
        'options_id_from',
        'options_id',
        'order_by',
        'order',
        'origin',
        'p',
        'padID',
        'page',
        'pages_id',
        'payment_status',
        'paypal_ipn_sort_order',
        'pID',
        'ppage',
        'product_type',
        'products_filter_name_model',
        'products_filter',
        'products_id',
        'products_options_id_all',
        'products_update_id',
        'profile',
        'ptID',
        'q',
        'read',
        'recip_count',
        'referral_code',
        'reports_page',
        'reset_categories_products_sort_order',
        'reset_editor',
        'reset_ez_sort_order',
        'reset_option_names_values_copier',
        'rID',
        's',
        'saction',
        'set',
        'set_display_categories_dropdown',
        'sID',
        'spage',
        'start_date',
        'status',
        't',
        'tID',
        'type',
        'uid',
        'update_action',
        'update_to',
        'user',
        'value_id',
        'value_page',
        'vcheck',
        'year',
        'za_lookup',
        'zID',
        'zone',
        'zpage',
        'coupon_copy_to'
    );
    self::registerGroup('admin', 'adminGetSaniGroup1', array(
        'entries' => $adminGetSaniGroup1,
        'source' => 'all',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[^\/0-9a-zA-Z_:@.-]/'
        )
    ));
    $this->notify('NOTIFY_REQUEST_BUILD_SANITIZERS_END');
  }
  /**
   */
  protected function legacySupport()
  {
    $_GET = $this->parameterBag ['get'];
    $_POST = $this->parameterBag ['post'];
    $_REQUEST = array_merge($_GET, $_POST);
  }
}
