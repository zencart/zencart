Feature: Customer Discount Coupons
  Test the creation of Discount Coupons

  @javascript
  Scenario: Create Discount Coupons
    Given I create a discount coupon "test10percent"
    Given I create a discount coupon "test10fixed"
    Given I create a discount coupon "test100fixed"
    Given I create a discount coupon "test100percent"
    Given I create a discount coupon "test10percentrestricted"
    Given I create a discount coupon "testfreeshipping"
