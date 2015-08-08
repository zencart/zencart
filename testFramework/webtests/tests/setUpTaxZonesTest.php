<?php
/**
 * set up some tax zones
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

/**
 * Class setUpTaxZonesTest
 */
class setUpTaxZonesTest extends CommonTestResources
{
    /**
     *
     */
    public function testSetupVAT()
    {
        $this->url('https://' . DIR_WS_ADMIN);
        $this->assertTextPresent('Admin Login');
        $this->byId('admin_name')->value(WEBTEST_ADMIN_NAME_INSTALL);
        $this->byId('admin_pass')->value(WEBTEST_ADMIN_PASSWORD_INSTALL);
        $continue = $this->byId('btn_submit');
        $continue->click();
        $this->assertTextPresent('Add Widget');
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=geo_zones&action=add');
        $this->byId('entry_field_geo_zone_name')->value("UK/VAT");
        $this->byId('entry_field_geo_zone_description')->value("United Kingdom VAT");
        $continue = $this->byId('btnsubmit');
        $continue->click();
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=geo_zones_detail&geo_zone_id=2');
        $addSubZone = $this->byLinkText('Add Zone Definition');
        $addSubZone->click();

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_countries_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("United K");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='United K']");
        $target1->click();
        sleep(1);
        $select2Mouse2 = $this->byXpath("//div[contains(@id, 's2id_entry_field_zone_name')]/descendant::a");
        $select2Mouse2->click();
        sleep(1);
        $this->byCss("#select2-drop input.select2-input")->value("All Zones");
        sleep(1);
        $target2 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='All Zones']");
        $target2->click();
        sleep(1);
        $continue = $this->byId('btnsubmit');
        $continue->click();

        $addSubZone = $this->byLinkText('Add Zone Definition');
        $addSubZone->click();

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_countries_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("Ireland");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='Ireland']");
        $target1->click();
        sleep(1);
        $select2Mouse2 = $this->byXpath("//div[contains(@id, 's2id_entry_field_zone_name')]/descendant::a");
        $select2Mouse2->click();
        sleep(1);
        $this->byCss("#select2-drop input.select2-input")->value("All Zones");
        sleep(1);
        $target2 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='All Zones']");
        $target2->click();
        sleep(1);
        $continue = $this->byId('btnsubmit');
        $continue->click();

        $addTaxRates = $this->byLinkText('Tax Rates');
        $addTaxRates->click();
        $addTaxRate = $this->byLinkText('Add Tax Rate');
        $addTaxRate->click();

        $this->byId("entry_field_tax_priority")->value("1");
        $this->byId("entry_field_tax_rate")->value("17.5");
        $this->byId("entry_field_tax_description")->value("VAT 17.5%");

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_tax_class_title')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("Taxable Goods");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='Taxable Goods']");
        $target1->click();
        sleep(1);

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_geo_zone_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("UK/VAT");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='UK/VAT']");
        $target1->click();
        sleep(1);

        $continue = $this->byId('btnsubmit');
        $continue->click();
    }

    /**
     *
     */
    public function testSetupCaliforniaTax()
    {
        $this->url('https://' . DIR_WS_ADMIN);
        $this->assertTextPresent('Admin Login');
        $this->byId('admin_name')->value(WEBTEST_ADMIN_NAME_INSTALL);
        $this->byId('admin_pass')->value(WEBTEST_ADMIN_PASSWORD_INSTALL);
        $continue = $this->byId('btn_submit');
        $continue->click();
        $this->assertTextPresent('Add Widget');
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=geo_zones&action=add');
        $this->byId('entry_field_geo_zone_name')->value("California");
        $this->byId('entry_field_geo_zone_description')->value("California Tax");
        $continue = $this->byId('btnsubmit');
        $continue->click();
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=geo_zones_detail&geo_zone_id=3');
        $addSubZone = $this->byLinkText('Add Zone Definition');
        $addSubZone->click();

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_countries_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("United States");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='United States']");
        $target1->click();
        sleep(1);
        $select2Mouse2 = $this->byXpath("//div[contains(@id, 's2id_entry_field_zone_name')]/descendant::a");
        $select2Mouse2->click();
        sleep(1);
        $this->byCss("#select2-drop input.select2-input")->value("California");
        sleep(1);
        $target2 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='California']");
        $target2->click();
        sleep(1);
        $continue = $this->byId('btnsubmit');
        $continue->click();

        $addTaxRates = $this->byLinkText('Tax Rates');
        $addTaxRates->click();
        $addTaxRate = $this->byLinkText('Add Tax Rate');
        $addTaxRate->click();

        $this->byId("entry_field_tax_priority")->value("1");
        $this->byId("entry_field_tax_rate")->value("12.75");
        $this->byId("entry_field_tax_description")->value("CA TAX 12.75%");

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_tax_class_title')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("Taxable Goods");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='Taxable Goods']");
        $target1->click();
        sleep(1);

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_geo_zone_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("California");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='California']");
        $target1->click();
        sleep(1);

        $continue = $this->byId('btnsubmit');
        $continue->click();
    }

    /**
     *
     */
    public function testSetupPostageTax()
    {
        $this->url('https://' . DIR_WS_ADMIN);
        $this->assertTextPresent('Admin Login');
        $this->byId('admin_name')->value(WEBTEST_ADMIN_NAME_INSTALL);
        $this->byId('admin_pass')->value(WEBTEST_ADMIN_PASSWORD_INSTALL);
        $continue = $this->byId('btn_submit');
        $continue->click();
        $this->assertTextPresent('Add Widget');
        $this->url('https://' . DIR_WS_ADMIN . 'index.php?cmd=tax_classes&action=add');
        $this->byId("entry_field_tax_class_title")->value("Taxable Postage");
        $this->byId("entry_field_tax_class_description")->value("Taxable Postage");
        $continue = $this->byId('btnsubmit');
        $continue->click();

        $addTaxRates = $this->byLinkText('Tax Rates');
        $addTaxRates->click();
        $addTaxRate = $this->byLinkText('Add Tax Rate');
        $addTaxRate->click();

        $this->byId("entry_field_tax_priority")->value("1");
        $this->byId("entry_field_tax_rate")->value("19");
        $this->byId("entry_field_tax_description")->value("POSTAGE TAX 19%");

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_tax_class_title')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("Taxable Postage");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='Taxable Postage']");
        $target1->click();
        sleep(1);

        $select2Mouse1 = $this->byXpath("//div[contains(@id, 's2id_entry_field_geo_zone_name')]/descendant::a");
        $select2Mouse1->click();
        $this->byCss("#select2-drop input.select2-input")->value("Florida");
        sleep(1);
        $target1 = $this->byXpath("//div[contains(@class, 'select2-result-label') and .//text()='Florida']");
        $target1->click();
        sleep(1);

        $continue = $this->byId('btnsubmit');
        $continue->click();
    }
}
