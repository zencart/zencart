Feature: Installer
  Test the Zen Cart Installer

  @javascript
  Scenario: Load the default page with a clean configure.php
    Then I should reset install
    Given I am on "/"
    Then I should see "Hello. Thank you for loading Zen Cart"

  @javascript
  Scenario: Attempt Basic Install, Load Admin and do the Admin Setup Wizard
    Given I am on "/zc_install/"
    Then I should see "System Inspection"
    And I press button "btnsubmit"

    Then I should see "Agree to license terms"
    And I check "agreeLicense"
    And I press button "btnsubmit"
    Then I wait to see "Load Demo Data"
    And I check "demoData"

    When I fill in "db_host" with <param>"db_host"
    And I fill in "db_user" with <param>"db_user"
    And I fill in "db_password" with <param>"db_password"
    And I fill in "db_name" with <param>"db_name"
    And I press button "btnsubmit"

    Then I wait to see "Admin User Settings"
    When I fill in "admin_user" with <param>"installer_admin_name"
    And I fill in "admin_email" with <param>"store_owner_email"
    And I fill in "admin_email2" with <param>"store_owner_email"
    And I press button "btnsubmit"

    Then I should see "Installation completed"

    Given I do a first admin login with <param>"admin_user_main", <param>"admin_password_install", <param>"admin_password_main"
    Then I should see "Initial Setup Wizard"

    Then I fill in "store_name" with <param>"store_name"
    And I fill in "store_owner" with <param>"store_owner"
    And I fill in "store_owner_email" with <param>"store_owner_email"
    And I press button "submit_button"
    Then I should see "Add Widget"

  @javascript
  Scenario: Enable COD payment method

    Given I do a standard admin login with <param>"admin_user_main", <param>"admin_password_main"
    Then I visit "admin/index.php?cmd=modules&set=payment&module=cod"
    Then I press button by name "installButton"

  @javascript
  Scenario: After Install, Check some Catalog Pages
    Given I am on "/"
    Then I should see "Sales Message Goes Here"

    Given I am on "index.php?main_page=index&cPath=1"
    Then I should see "CDROM Drives"
    And I should see "Keyboards"

  @javascript
  Scenario: After Install, Enable Swedish Kroner
    Given I add swedish kroner
