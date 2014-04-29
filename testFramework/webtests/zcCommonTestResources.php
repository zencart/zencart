<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zcCommonTestResources.php 19138 2011-07-18 17:37:21Z wilt $
 */
/**
 *
 * @package tests
 */
if (defined('TRAVIS') && TRAVIS ==='true') {
  require_once 'vendor/autoload.php';
} else {
  require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
}

// bamboo is currently configured for PHPUnit_Extensions_SeleniumTestCase
#class zcCommonTestResources extends PHPUnit_Extensions_SeleniumTestCase
class zcCommonTestResources extends Sauce\Sausage\WebDriverTestCase
{
  protected $coverageScriptUrl, $start_url = '';
  protected $paypalSandboxLoginEnabled = FALSE;
  protected static $compoundDone = FALSE;
  private $dbActive;
  private $dbLink;
  private $VATcreated = FALSE;
//   public static $browsers = array(
//           // run FF15 on Windows 8 on Sauce
//           array(
//                   'browserName' => 'firefox',
//                   'desiredCapabilities' => array(
//                           'version' => '15',
//                           'platform' => 'Windows 2012',
//                   )
//           ),
//           // run Chrome on Linux on Sauce
//           array(
//                   'browserName' => 'chrome',
//                   'desiredCapabilities' => array(
//                           'platform' => 'Linux'
//                   )
//           ),
//           // run Mobile Safari on iOS
//           //array(
//           //'browserName' => '',
//           //'desiredCapabilities' => array(
//           //'app' => 'safari',
//           //'device' => 'iPhone Simulator',
//           //'version' => '6.1',
//           //'platform' => 'Mac 10.8',
//           //)
//           //)//,
//           // run Chrome locally
//           //array(
//           //'browserName' => 'chrome',
//           //'local' => true,
//           //'sessionStrategy' => 'shared'
//           //)
//           );

  public function setUp()
  {
    $this->coverageScriptUrl = "http://" . BASE_URL . "/phpunit_coverage.php";
    $this->start_url = "http://" . BASE_URL . "/index.php";

    if (!defined('TRAVIS')) {
      $this->setBrowser(SELENIUM_BROWSER);
    }
    $this->setBrowserUrl($this->start_url);
    $this->generalSqlInstallStuff();
  }
  /**
   * This is just a quick handy tool for running a quick DB query
   */
  public function doDbQuery($sql = '')
  {
    if ($sql == '') return FALSE;
    if (!$this->dbActive) {
      $this->dbLink = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
      if ($this->dbLink) {
        mysqli_select_db($this->dbLink, DB_DBNAME);
        $this->dbActive = TRUE;
      } else {
        echo 'MySQL error: ' . mysqli_errno($this->dbLink) . ' ' . mysqli_error($this->dbLink);
        sleep(10);
        die('Script aborted. ' . __FILE__ . ':('.__LINE__.')');
      }
    }
    $result = mysqli_query($this->dbLink, $sql);
    //mysqli_close($this->dbLink);
    return $result;
  }
  public function generalSqlInstallStuff()
  {
    $sql = "INSERT INTO currencies VALUES ('','Swedish Krona','SEK','SEK','',',','','2','1', now());";
    $this->doDbQuery($sql);
  }
  public function switchConfigurationValue($configKey, $configValue)
  {
    $sql = "SELECT configuration_value FROM " . DB_PREFIX . "configuration WHERE configuration_key = '" . $configKey . "'";
    $q = $this->doDbQuery($sql);
    $result = mysqli_fetch_assoc($q);
    $original = $result['configuration_value'];
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . $configValue . "' where configuration_key = '" . $configKey . "'");
    return $original;
  }
  /**
   * Enable tax-included pricing (mainly for VAT sites)
   */
  public function switchToTaxInclusive()
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = 'true' where configuration_key = 'DISPLAY_PRICE_WITH_TAX'");
  }
  /**
   * Disable tax-included pricing (typical for north america)
   */
  public function switchToTaxNonInclusive()
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = 'false' where configuration_key = 'DISPLAY_PRICE_WITH_TAX'");
  }
  function switchItemShippingTax($mode = 'on')
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? '1' : '0') . "' where configuration_key = 'MODULE_SHIPPING_ITEM_TAX_CLASS'");
  }
  function switchFlatShippingTax($mode = 'on')
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_FLAT_TAX_CLASS'");
  }
  function switchSplitTaxMode($mode = 'on')
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? 'true' : 'false') . "' where configuration_key = 'SHOW_SPLIT_TAX_CHECKOUT'");
  }
  function switchAddToCartRedirect($mode = 'true')
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . $mode . "' where configuration_key = 'DISPLAY_CART'");
  }
  public function createAdminSSLOverride()
  {
    $fp = fopen(DIR_FS_ADMIN . 'includes/local/configure.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("HTTP_SERVER", "https://' . SERVER_NAME . '");' . PHP_EOL);
    fputs($fp, 'DEFINE("ENABLE_SSL_CATALOG", "true");' . PHP_EOL);
    fclose($fp);
  }
  public function createCatalogSSLOverride()
  {
    $fp = fopen(DIR_FS_CATALOG . 'includes/local/configure.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("ENABLE_SSL", "true");' . PHP_EOL);
    fclose($fp);
  }
  public function createAdminNonSSLOverride()
  {
    $fp = fopen(DIR_FS_ADMIN . 'includes/local/configure.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("HTTP_SERVER", "http://' . SERVER_NAME . '");' . PHP_EOL);
    fputs($fp, 'DEFINE("ENABLE_SSL_CATALOG", "false");' . PHP_EOL);
    fclose($fp);
  }
  public function createTwoFactorAuthenticationOverrideTrue()
  {
    $fp = fopen(DIR_FS_ADMIN . 'includes/extra_configures/not_for_release1.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE", "twoFactorAuthenticationFunction");' . PHP_EOL);
    fputs($fp, 'DEFINE("TWO_FACTOR_AUTHENTICATION_RESULT", "true");' . PHP_EOL);
    fclose($fp);
  }
  public function createTwoFactorAuthenticationOverrideFalse()
  {
    $fp = fopen(DIR_FS_ADMIN . 'includes/extra_configures/not_for_release1.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE", "twoFactorAuthenticationFunction");' . PHP_EOL);
    fputs($fp, 'DEFINE("TWO_FACTOR_AUTHENTICATION_RESULT", "false");' . PHP_EOL);
    fclose($fp);
  }
  public function removeTwoFactorAuthenticationOverride()
  {
    unlink(DIR_FS_ADMIN . 'includes/extra_configures/not_for_release1.php');
  }
  public function setupCompoundTaxes()
  {
    if (!self::$compoundDone)
    {
      $this->doDbQuery("INSERT INTO " . DB_PREFIX . "geo_zones (geo_zone_name, geo_zone_description, last_modified, date_added) VALUES ('Canada', 'Canada Compound', NULL, NOW())");
      $geoZone = mysqli_insert_id($this->dbLink);
      $this->doDbQuery("INSERT INTO " . DB_PREFIX . "zones_to_geo_zones (zone_country_id, zone_id, geo_zone_id, last_modified, date_added) VALUES (38, 0, $geoZone, NULL, NOW())");
      $this->doDbQuery("INSERT INTO " . DB_PREFIX . "tax_rates (tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES ($geoZone, 1, 1, '3.000', 'CAD Compound 1', NULL, NOW())");
      $this->doDbQuery("INSERT INTO " . DB_PREFIX . "tax_rates (tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES ($geoZone, 1, 2, '8.000', 'CAD Compound 2', NULL, NOW())");
      self::$compoundDone = TRUE;
    }
  }
  public function setTaxPrioritySame()
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "tax_rates set tax_priority = 1 WHERE tax_description = 'CAD Compound 2'");
  }
  public function setTaxPriorityDifferent()
  {
    $this->doDbQuery("UPDATE " . DB_PREFIX . "tax_rates set tax_priority = 2 WHERE tax_description = 'CAD Compound 2'");
  }
  public function doScreenshot($imageName)
  {
    if (defined('DO_SCREENSHOT') && DO_SCREENSHOT == TRUE)
    {
      $this->captureEntirePageScreenshot(SCREENSHOT_PATH . $imageName);
    }
  }
  public function resetAdminPassword()
  {
    $sql = "update " . DB_PREFIX . "admin set admin_name = '" . WEBTEST_ADMIN_NAME_INSTALL . "', admin_email = '" . WEBTEST_ADMIN_EMAIL . "', admin_pass = '" . zen_encrypt_password(WEBTEST_ADMIN_PASSWORD_INSTALL) . "', reset_token = '" . (time() + (72 * 60 * 60)) . "}" . zen_encrypt_password(WEBTEST_ADMIN_PASSWORD_INSTALL) . "' where admin_id = 1";
    $this->doDbQuery($sql);
  }
  public function renameAdmin()
  {    $adminDirectoryList = array();

    $ignoreArray = array('.', '..', 'cache', 'logs', 'installer', 'zc_install', 'includes', 'testFramework', 'editors', 'extras', 'images', 'docs', 'pub', 'email', 'download', 'media');
    $d = @dir(DIR_FS_ROOT);
    while (false !== ($entry = $d->read())) {
      if (is_dir(DIR_FS_ROOT . $entry) && !in_array($entry, $ignoreArray))
      {
        if (file_exists(DIR_FS_ROOT . $entry . '/' . 'banner_manager.php'))
        {
          $adminDirectoryList[] = DIR_FS_ROOT . $entry;
        }
      }
    }
    $adminDir = $adminDirectoryList[0];
    rename ($adminDir, DIR_FS_ADMIN);
  }
}
function zen_encrypt_password($plain)
{
  $password = '';

  for ($i = 0; $i < 10; $i++)
  {
    $password .= zen_rand();
  }

  $salt = substr(md5($password), 0, 2);

  $password = md5($salt . $plain) . ':' . $salt;

  return $password;
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
