<?php
/**
 * Parent class used for PHPUnit testing of URL Generation.
 *
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('zcTestCase.php');
/**
 * Testing Library
 */
class zcUrlGenerationTestCase extends zcTestCase
{

  public function setUp()
  {
    // Configure URL environment
    if(!defined('HTTP_SERVER'))
      define('HTTP_SERVER', 'http://zencart-git.local');
    if(!defined('HTTPS_SERVER'))
      define('HTTPS_SERVER', 'https://zencart-git.local');
    if(!defined('DIR_WS_CATALOG'))
      define('DIR_WS_CATALOG', '/');
    if(!defined('DIR_WS_HTTPS_CATALOG'))
      define('DIR_WS_HTTPS_CATALOG', '/ssl/');

    // Configure required language defines
    if(!defined('CONNECTION_TYPE_UNKNOWN'))
      define('CONNECTION_TYPE_UNKNOWN', 'Unknown Connection \'%s\' Found: %s');

    if(!defined('IS_ADMIN_FLAG'))
      define('IS_ADMIN_FLAG', false);

    parent::setUp();

    require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'filenames.php');
    if(IS_ADMIN_FLAG)
    {
      if(!defined('HTTP_CATALOG_SERVER'))
        define('HTTP_CATALOG_SERVER', 'http://zencart-git.local');
      if(!defined('HTTPS_CATALOG_SERVER'))
        define('HTTPS_CATALOG_SERVER', 'https://zencart-git.local');

      require_once(DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'html_output.php');

      if(!function_exists('zen_session_name'))
        eval('function zen_session_name($name = \'\') { return \'zenadminid\'; }');
    }
    else
    {
      require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');

      if(!function_exists('zen_session_name'))
        eval('function zen_session_name($name = \'\') { return \'zenid\'; }');
    }
    require_once('zcURLTestObserver.php');

    if(!function_exists('zen_session_id'))
      eval('function zen_session_id($sessid = \'\') { return \'1234567890\'; }');

    // Load some required globals
    if(!array_key_exists('zco_notifier', $GLOBALS))
      $GLOBALS['zco_notifier'] = new notifier();
    if(!array_key_exists('request_type', $GLOBALS))
      $GLOBALS['request_type'] = 'SSL';
    if(!array_key_exists('session_started', $GLOBALS))
      $GLOBALS['session_started'] = false;
    if(!array_key_exists('http_domain', $GLOBALS))
      $GLOBALS['http_domain'] = zen_get_top_level_domain(HTTP_SERVER);
    if(!array_key_exists('https_domain', $GLOBALS))
      $GLOBALS['https_domain'] = zen_get_top_level_domain(HTTPS_SERVER);


    // Create the observer
    if(!array_key_exists('zcURLTestObserver', $GLOBALS))
      $GLOBALS['zcURLTestObserver'] = new zcURLTestObserver();
  }

  protected function assertURLGenerated($url, $expected)
  {
    return $this->assertTrue(
      $url == $expected,
      'An incorrect URL was generated:' . PHP_EOL . $url . PHP_EOL .
      'The expected URL was:'  . PHP_EOL . $expected . PHP_EOL
    );
  }
}