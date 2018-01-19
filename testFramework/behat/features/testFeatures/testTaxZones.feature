Feature: Add Tax Zones
  Add Tax Zones

  @javascript
  Scenario: Set up VAT

    Given I do a standard admin login with <param>"admin_user_main", <param>"admin_password_main"
    Then I should see "Add Widget"

    Given I am on "admin/index.php?cmd=geo_zones&action=add"
    And I fill in "entry_field_geo_zone_name" with "UK/VAT"
    And I fill in "entry_field_geo_zone_description" with "United Kingdom VAT"
    Then I press button "btnsubmit"

    Given I am on "admin/index.php?cmd=geo_zones_detail&geo_zone_id=2"
    Then I follow "Add Zone Definition"
    Then I use select2 to fill in field, search value with "countries_name", "United Kingdom"
    Then I use select2 to fill in field, search value with "zone_name", "All Zones"
    Then I press button "btnsubmit"

    Then I follow "Add Zone Definition"

    Then I use select2 to fill in field, search value with "countries_name", "Ireland"
    Then I use select2 to fill in field, search value with "zone_name", "All Zones"
    Then I press button "btnsubmit"

    Given I am on "admin/index.php?cmd=tax_rates&action=add"
    Then I fill in "entry_field_tax_priority" with "1"
    Then I fill in "entry_field_tax_rate" with "17.5"
    Then I fill in "entry_field_tax_description" with "VAT 17.5%"

    Then I use select2 to fill in field, search value with "tax_class_title", "Taxable Goods"
    Then I use select2 to fill in field, search value with "geo_zone_name", "UK/VAT"
    Then I press button "btnsubmit"

  @javascript
  Scenario: Set up California Tax

    Given I do a standard admin login with <param>"admin_user_main", <param>"admin_password_main"
    Then I should see "Add Widget"

    Given I am on "admin/index.php?cmd=geo_zones&action=add"
    And I fill in "entry_field_geo_zone_name" with "California"
    And I fill in "entry_field_geo_zone_description" with "California Tax"
    Then I press button "btnsubmit"

    Given I am on "admin/index.php?cmd=geo_zones_detail&geo_zone_id=3"
    Then I follow "Add Zone Definition"

    Then I use select2 to fill in field, search value with "countries_name", "United States"
    Then I use select2 to fill in field, search value with "zone_name", "California"

    Then I press button "btnsubmit"


    Given I am on "admin/index.php?cmd=tax_rates&action=add"
    Then I fill in "entry_field_tax_priority" with "1"
    Then I fill in "entry_field_tax_rate" with "12.75"
    Then I fill in "entry_field_tax_description" with "CA TAX 12.75%"

    Then I use select2 to fill in field, search value with "tax_class_title", "Taxable Goods"
    Then I use select2 to fill in field, search value with "geo_zone_name", "California"
    Then I press button "btnsubmit"

  @javascript
  Scenario: Set up Postage Tax

    Given I do a standard admin login with <param>"admin_user_main", <param>"admin_password_main"
    Then I should see "Add Widget"

    Given I am on "admin/index.php?cmd=tax_classes&action=add"
    And I fill in "entry_field_tax_class_title" with "Taxable Postage"
    And I fill in "entry_field_tax_class_description" with "Taxable Postage"
    Then I press button "btnsubmit"
    Given I am on "admin/index.php?cmd=tax_rates&action=add"
    Then I fill in "entry_field_tax_priority" with "1"
    Then I fill in "entry_field_tax_rate" with "19"
    Then I fill in "entry_field_tax_description" with "POSTAGE TAX 19%"
    Then I use select2 to fill in field, search value with "tax_class_title", "Taxable Postage"
    Then I use select2 to fill in field, search value with "geo_zone_name", "Florida"
    Then I press button "btnsubmit"

