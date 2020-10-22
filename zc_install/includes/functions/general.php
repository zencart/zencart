<?php
/**
 * general functions
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 17 Modified in v1.5.7 $
 */

if (!defined('TABLE_UPGRADE_EXCEPTIONS')) define('TABLE_UPGRADE_EXCEPTIONS','upgrade_exceptions');

function zen_get_select_options($optionList, $setDefault)
{
  $optionString = "";
  foreach ($optionList as $option)
  {
    $optionString .= '<option value="' . $option['id'] . '"';
    if ($setDefault == $option['id']) $optionString .= " SELECTED ";
    $optionString .= '>' . $option['text'];
    $optionString .='</option>';
  }
  return $optionString;
}

  function zen_not_null($value) {
    if (null === $value) {
        return false;
    }
    if (is_array($value)) {
        return count($value) > 0;
    }
    if (is_a($value, 'queryFactoryResult')) {
        return count($value->result) > 0;
    }
    return trim($value) !== '' && strtolower($value) != 'null';
  }

  function logDetails($details, $location = "General") {
      if (!isset($_SESSION['logfilename']) || $_SESSION['logfilename'] == '') $_SESSION['logfilename'] = date('m-d-Y_h-i-s-') . zen_create_random_value(6);
      if ($fp = @fopen(DEBUG_LOG_FOLDER . '/zcInstallLog_' . $_SESSION['logfilename'] . '.log', 'a')) {
        fwrite($fp, '---------------' . "\n" . date('M d Y G:i') . ' -- ' . $location . "\n" . $details . "\n\n");
        fclose($fp);
      }
    }

   function zen_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }
  function zen_get_document_root() {
    $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
    if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
    $dir_fs_www_root = str_replace(array('\\','//'), '/', $dir_fs_www_root);
    return $dir_fs_www_root;
  }
  function zen_get_http_server()
  {
    $host = $_SERVER['HTTP_HOST'];
    $script = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    if (substr($script[0], 0, 1) == '~') {
      $host .= '/' . $script[0];
    }
    return $host;
  }
  function zen_parse_url($url, $element = 'array', $detect_tilde = false)
  {
    // Read the various elements of the URL, to use in auto-detection of admin foldername (basically a simplified parse_url equivalent which automatically supports ports and uncommon TLDs)
    $t1 = array();
    // scheme
    $s1 = explode('://', $url);
    $t1['scheme'] = $s1[0];
    // host
    $s2 = explode('/', trim($s1[1], '/'));
    $t1['host'] = $s2[0];
    array_shift($s2);
    // adjust host to accommodate /~username shared-ssl scenarios
    if ($detect_tilde && isset($s2[0]) && strpos($s2[0], '~') === 0) {
      $t1['host'] .= '/' . $s2[0];
      // array_shift also therefore removes it from ['path'] below
      array_shift($s2);
    }
    // path/uri
    $t1['path'] = implode('/', $s2);
    $p1 = ($t1['path'] != '') ? '/' . $t1['path'] : '';

    switch($element) {
      case 'scheme':
      case 'host':
      case 'path':
        return $t1[$element];
      case '/path':
        return $p1;
      case 'array':
      default:
        return $t1;
    }
  }

  function zen_sanitize_request()
  {
    foreach ($_POST as $key => $value)
    {
      $_POST[htmlspecialchars($key, ENT_COMPAT, 'UTF-8', FALSE)] = addslashes($value);
    }
  }
  /**
   * Returns a string with conversions for security.
   * @param string The string to be parsed
   * @param string contains a string to be translated, otherwise just quote is translated
   * @param boolean Do we run htmlspecialchars over the string
   */
  function zen_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string, ENT_COMPAT, 'utf-8', TRUE);
    } else {
      if ($translate == false) {
        return zen_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return zen_parse_input_field_data($string, $translate);
      }
    }
  }

  /**
   * Returns a string with conversions for security.
   *
   * Simply calls the zen_ouput_string function
   * with parameters that run htmlspecialchars over the string
   * and converts quotes to html entities
   *
   * @param string The string to be parsed
   */
  function zen_output_string_protected($string) {
    return zen_output_string($string, false, true);
  }

  function zen_get_install_languages_list($lng)
  {
    global $languagesInstalled;
    $optionString = "";
    foreach ($languagesInstalled as $code=>$language)
    {
      $optionString .= '<option value="' . $code . '"';
      if ($code == $lng)
      {
        $optionString .= " SELECTED ";
      }
      $optionString .= '>' . $language['displayName'];
      $optionString .= "</option>";
    }
    return $optionString;
  }

  /**
   * helper function to detect current site URI info
   * @return array($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog)
   */
  function getDetectedURIs($adminDir = 'admin') {
    global $request_type;
    if (isset($_POST['adminDir'])) $adminDir = zen_output_string_protected($_POST['adminDir']);
    $documentRoot = zen_get_document_root();
    $url = ($request_type == 'SSL' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/zc_install/index.php', '', $_SERVER['SCRIPT_NAME']);
    $httpServer = zen_parse_url($url, 'host', true);
    $adminServer = ($request_type == 'SSL') ? 'https://' : 'http://';
    $adminServer .= $httpServer;
    $catalogHttpServer = ($request_type == 'SSL' ? 'https://' : 'http://') . $httpServer;
    $catalogHttpUrl = ($request_type == 'SSL' ? 'https://' :'http://') . $httpServer . '/' . zen_parse_url($url, 'path', true);
    $catalogHttpsServer = 'https://' . $httpServer;
    $catalogHttpsUrl = 'https://' . $httpServer . '/' . zen_parse_url($url, 'path', true);
    $dir_ws_http_catalog = str_replace($catalogHttpServer, '', $catalogHttpUrl) .'/';
    $dir_ws_https_catalog = str_replace($catalogHttpsServer, '', $catalogHttpsUrl) . '/';

    return array($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog);
  }


