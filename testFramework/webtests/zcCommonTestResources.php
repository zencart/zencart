<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zcCommonTestResources.php 19138 2011-07-18 17:37:21Z wilt $
 */
/**
 *
 * @package tests
 */
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
class zcCommonTestResources extends PHPUnit_Extensions_SeleniumTestCase
{
  protected $coverageScriptUrl = 'http://" . BASE_URL . "phpunit_coverage.php';
  protected $paypalSandboxLoginEnabled = FALSE;
  protected static $compoundDone = FALSE;
  private $dbActive;
  private $dbLink;
  private $VATcreated = FALSE;

  protected function setUp()
  {
    $this->setBrowser(SELENIUM_BROWSER);
    $this->setBrowserUrl('http://' . BASE_URL);
  }
  /**
   * This is just a quick handy tool for running a quick DB query
   */
  public function doDbQuery($sql = '')
  {
    if ($sql == '') return FALSE;
    if (!$this->dbActive) {
      $this->dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS);
      if ($this->dbLink) {
        mysql_select_db(DB_DBNAME, $this->dbLink);
        $this->dbActive = TRUE;
      } else {
        echo 'MySQL error: ' . mysql_errno() . ' ' . mysql_error();
        sleep(10);
        die('Script aborted. ' . __FILE__ . ':('.__LINE__.')');
      }
    }
    $result = mysql_query($sql, $this->dbLink);
    //mysql_close($this->dbLink);
    return $result;
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
  public function createAdminSSLOverride()
  {
    $fp = fopen(DIR_FS_ADMIN . 'includes/local/configure.php', 'w');
    fputs($fp, '<?php' . PHP_EOL);
    fputs($fp, 'DEFINE("ENABLE_SSL_ADMIN", "true");' . PHP_EOL);
    fputs($fp, 'DEFINE("HTTP_SERVER", "https://' . SERVER_NAME . '");' . PHP_EOL);
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
    fputs($fp, 'DEFINE("ENABLE_SSL_ADMIN", "false");' . PHP_EOL);
    fputs($fp, 'DEFINE("HTTP_SERVER", "http://' . SERVER_NAME . '");' . PHP_EOL);
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
  public function setupCompoundTaxes()
  {
    if (!self::$compoundDone)
    {
      $this->doDbQuery("INSERT INTO " . DB_PREFIX . "geo_zones (geo_zone_name, geo_zone_description, last_modified, date_added) VALUES ('Canada', 'Canada Compound', NULL, NOW())");
      $geoZone = mysql_insert_id();
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
}