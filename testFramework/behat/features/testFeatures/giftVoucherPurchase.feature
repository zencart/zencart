Feature: Gift Voucher Purchase Tests
  Test the purchase of gift vouchers

  @javascript
  Scenario: Purchase an initial GV amount

    Given I set a configuration value "MODULE_ORDER_TOTAL_GV_QUEUE", true
    Then I purchase a gift voucher queue on with <param>"default_customer_email", <param>"default_customer_password", "100"
    And I set a configuration value "MODULE_ORDER_TOTAL_GV_QUEUE", false


  Scenario: Make a purchase with a gift voucher

    Given I do a standard customer login with <param>'default_customer_email', <param>'default_customer_password'
    Then I visit "index.php?main_page=shopping_cart&action=empty_cart"
    Then I visit "index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now"
    Then I visit "index.php?main_page=checkout_flow"
    Then I click on the element with xpath "//*[@id='checkoutShipping']/form/div[5]/input"
    Then I fill in "cot_gv" with "100"
    Then I click on the element with xpath "//*[@id='paymentSubmit']/input"
    Then I should see "order Confirmation"
    Then I click on the element with xpath "//*[@id='btn_submit']"
    Then I should see "39.99"
    Then I should see "45.29"
    Then I click on the element with xpath "//*[@id='csNotifications']/form/div/input"


  Scenario: Make a purchase with a gift voucher where credit does not cover

    Given I do a standard customer login with <param>'default_customer_email', <param>'default_customer_password'
    Then I visit "index.php?main_page=shopping_cart&action=empty_cart"
    Then I visit "index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now"
    Then I visit "index.php?main_page=checkout_flow"
    Then I click on the element with xpath "//*[@id='checkoutShipping']/form/div[5]/input"
    Then I fill in "cot_gv" with "45.28"
    Then I click on the element with xpath "//*[@id='paymentSubmit']/input"
    Then I should see "Please select a payment method"



  Scenario: Make a purchase with a gift voucher where credit does not cover and with shipping tax

    Given I switch shipping tax "on"
    Given I do a standard customer login with <param>'default_customer_email', <param>'default_customer_password'
    Then I visit "index.php?main_page=shopping_cart&action=empty_cart"
    Then I visit "index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now"
    Then I visit "index.php?main_page=checkout_flow"
    Then I select "flat_flat" from "shipping"
    Then I click on the element with xpath "//*[@id='checkoutShipping']/form/div[5]/input"
    Then I fill in "cot_gv" with "48.73"
    Then I click on the element with xpath "//*[@id='paymentSubmit']/input"
    Then I should see "Please select a payment method"
    Given I switch shipping tax "off"


  Scenario: Make a purchase with a gift voucher using Swedish Kroner
    Given I set a configuration value "DEFAULT_CURRENCY", "SEK"
    Given I do a standard customer login with <param>'default_customer_email', <param>'default_customer_password'
    Then I visit "index.php?main_page=shopping_cart&action=empty_cart"
    Then I visit "index.php?main_page=product_info&cPath=1_9&products_id=3&action=buy_now"
    Then I visit "index.php?main_page=checkout_flow"
    Then I click on the element with xpath "//*[@id='checkoutShipping']/form/div[5]/input"
    Then I fill in "cot_gv" with "20.50"
    Then I click on the element with xpath "//*[@id='paymentSubmit']/input"
    Then I should see "SEK24,79"
    Given I set a configuration value "DEFAULT_CURRENCY", "USD"

  Scenario: Send Gift Voucher using Swedish Kroner
    Given I set a configuration value "DEFAULT_CURRENCY", "SEK"
    Given I do a standard customer login with <param>'default_customer_email', <param>'default_customer_password'
    Then I visit "index.php?main_page=gv_send"
    Then I fill in "to-name" with "Tom Bombadil"
    Then I fill in "email-address" with <param>"default_customer_email"
    Then I fill in "amount" with "20.50"
    Then I fill in "message-area" with "This is a test message"
    Then I submit the form "gv_send_send"
    Then I should see "Send Gift Certificate Confirmation"
    And I should see "SEK20,50"
    Then I submit the form "gv_send_process"
    Then I set a configuration value "DEFAULT_CURRENCY", "USD"
