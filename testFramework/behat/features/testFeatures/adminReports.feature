Feature: Admin Reports
  Test some Admin Reports

  @javascript
  Scenario: Admin Reports

    Given I do a standard admin login with <param>"admin_user_main", <param>"admin_password_main"
    And I visit "admin/index.php?cmd=stats_customers"
    Then I should see "Best Customer Orders-Total"
    And I visit "admin/index.php?cmd=stats_products_lowstock"
    Then I should see "Product Stock Report"

