<?php
/**
 * File contains common unit/web test resources
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zcCommonTestResources.php 19138 2011-07-18 17:37:21Z wilt $
 */
require_once 'vendor/autoload.php';

/**
 * Class CommonTestResources
 */
class CommonTestResources extends baseSeleniumTestClass
{

    use GiftVoucherTrait;
    use GroupDiscountTrait;
    use DiscountVouchersTrait;
    use LowOrderFeeTrait;

//    protected $paypalSandboxLoginEnabled = false;
    protected static $compoundDone = false;
    private $dbActive;
    private $dbLink;

//    private $VATcreated = false;


    public function doDbQuery($sql = '')
    {
        if ($sql == '') {
            return false;
        }
        if (!$this->dbActive) {
            $this->dbLink = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
            if ($this->dbLink) {
                mysqli_select_db($this->dbLink, DB_DBNAME);
                $this->dbActive = true;
            } else {
                echo 'MySQL error: ' . mysqli_errno($this->dbLink) . ' ' . mysqli_error($this->dbLink);
                sleep(10);
                die('Script aborted. ' . __FILE__ . ':(' . __LINE__ . ')');
            }
        }
        $result = mysqli_query($this->dbLink, $sql);

        //mysqli_close($this->dbLink);
        return $result;
    }

    public function getCustomerIdFromEmail($customerEmail)
    {
        $sql = "SELECT customers_id FROM " . DB_PREFIX . "customers WHERE customers_email_address = '" . $customerEmail . "'";
        $q = $this->doDbQuery($sql);
        if ($q->num_rows == 0) {
            return false;
        }
        $result = mysqli_fetch_assoc($q);

        return $result['customers_id'];
    }

    public function loginStandardCustomer($email, $password)
    {
        $this->url('https://' . BASE_URL . 'index.php?main_page=login');
        $this->byId('login-email-address')->value($email);
        $this->byId('login-password')->value($password);
        $this->byCss('input[type="image"]')->click();
    }

    public function loginStandardAdmin($adminName, $adminPass)
    {
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=logoff');
        $this->url('https://' . DIR_WS_ADMIN);
        $this->assertTextPresent('Admin Login');
        $this->byId('admin_name')->value($adminName);
        $this->byId('admin_pass')->value($adminPass);
        $continue = $this->byId('btn_submit');
        $continue->click();
        $this->assertTextPresent('Add Widget');
    }

    public function switchToTaxInclusive()
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration SET configuration_value = 'true' where configuration_key = 'DISPLAY_PRICE_WITH_TAX'";
        $this->doDbQuery($sql);
    }

    public function switchToTaxNonInclusive()
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration SET configuration_value = 'false' where configuration_key = 'DISPLAY_PRICE_WITH_TAX'";
        $this->doDbQuery($sql);
    }

    public function setConfigurationValue($configKey, $configValue)
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . $configValue . "' where configuration_key = '" . $configKey . "'";
        $this->doDbQuery($sql);
    }

    function switchItemShippingTax($mode = 'on')
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_ITEM_TAX_CLASS'";
        $this->doDbQuery($sql);
    }

    function switchFlatShippingTax($mode = 'on')
    {
        $sql = "UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? '2' : '0') . "' where configuration_key = 'MODULE_SHIPPING_FLAT_TAX_CLASS'";
        $this->doDbQuery($sql);
    }

    function switchSplitTaxMode($mode = 'on')
    {
        $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '" . ($mode == 'on' ? 'true' : 'false') . "' where configuration_key = 'SHOW_SPLIT_TAX_CHECKOUT'");
    }

    public function setupCompoundTaxes()
    {
        if (!self::$compoundDone) {
            $this->doDbQuery("INSERT INTO " . DB_PREFIX . "geo_zones (geo_zone_name, geo_zone_description, last_modified, date_added) VALUES ('Canada', 'Canada Compound', NULL, NOW())");
            $geoZone = mysqli_insert_id($this->dbLink);
            $this->doDbQuery("INSERT INTO " . DB_PREFIX . "zones_to_geo_zones (zone_country_id, zone_id, geo_zone_id, last_modified, date_added) VALUES (38, 0, $geoZone, NULL, NOW())");
            $this->doDbQuery("INSERT INTO " . DB_PREFIX . "tax_rates (tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES ($geoZone, 1, 1, '3.000', 'CAD Compound 1', NULL, NOW())");
            $this->doDbQuery("INSERT INTO " . DB_PREFIX . "tax_rates (tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES ($geoZone, 1, 2, '8.000', 'CAD Compound 2', NULL, NOW())");
            self::$compoundDone = true;
        }
    }
}
