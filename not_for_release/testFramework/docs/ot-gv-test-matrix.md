# `ot_gv` Test Matrix

This document inventories the current automated coverage for `includes/modules/order_total/ot_gv.php` and related storefront flows that exercise gift-voucher redemption.

It is aimed at answering three questions quickly:

1. Which tests directly exercise `ot_gv`?
2. Which configuration flags does each test run under?
3. What calculation path is each test validating?

## Shared Merchandise Fixture

Almost every storefront calculation test in the GV suite uses the same cart fixture from [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:493):

- customer profile: `florida-basic1`
- product added to cart: `products_id = 3`
- attribute selection: `1_9`
- quantity: `1`

That fixture produces these baseline amounts:

- merchandise subtotal, tax-exclusive: `39.99`
- merchandise subtotal, tax-inclusive at Florida 7%: `42.79`
- item shipping, tax-exclusive: `2.50`
- item shipping, tax-inclusive when taxable at 10%: `2.75`
- flat shipping, tax-exclusive: `5.00`
- flat shipping, tax-inclusive when taxable at 10%: `5.50`
- merchandise tax only: `2.80`
- merchandise tax plus taxable item shipping tax: `3.05`
- merchandise tax plus taxable flat shipping tax: `3.30`

So the standard checkout totals used throughout this document are:

- non-tax-inclusive display, non-taxable shipping: `39.99 + 2.50 + 2.80 = 45.29`
- non-tax-inclusive display, taxable shipping: `39.99 + 2.50 + 3.05 = 45.54`
- tax-inclusive display, taxable shipping: `42.79 + 2.75 = 45.54`
- non-tax-inclusive display, taxable flat shipping: `39.99 + 5.00 + 3.30 = 48.29`
- tax-inclusive display, taxable flat shipping: `42.79 + 5.50 = 48.29`

When a test says "exclude tax", the intended eligible voucher base is usually:

- `39.99` when shipping is also excluded
- `42.49` when shipping is included but tax is excluded

When a test says "exclude shipping" in the taxable-shipping cases, the intended eligible voucher base is:

- `42.79` regardless of whether display is tax-inclusive or tax-exclusive
- reason: the voucher should cover merchandise plus merchandise tax, but not shipping nor shipping tax

## Core Flags And Inputs

The calculation behavior in `ot_gv` is mainly driven by these settings:

- `MODULE_ORDER_TOTAL_GV_INC_TAX`
  - `true`: voucher can cover tax.
  - `false`: tax is excluded from the voucher base.
- `MODULE_ORDER_TOTAL_GV_INC_SHIPPING`
  - `true`: voucher can cover shipping.
  - `false`: shipping is excluded from the voucher base.
- `MODULE_ORDER_TOTAL_GV_CALC_TAX`
  - `None`: do not recalculate tax from the voucher deduction.
  - `Standard`: prorate tax-group deductions from the eligible voucher base.
  - `Credit Note`: treat the voucher as a credit-note style reduction using `MODULE_ORDER_TOTAL_GV_TAX_CLASS`.
- `DISPLAY_PRICE_WITH_TAX`
  - `true`: shipping display amount already includes shipping tax.
  - `false`: shipping display amount excludes shipping tax.
- Shipping tax toggles used in tests
  - `switchItemShippingTax('on')`: item shipping module uses taxable shipping.
  - `switchFlatShippingTax('on')`: flat shipping module uses taxable shipping.

Unless a test overrides them, `GiftVoucherRedeemTest::setUp()` starts with:

- `MODULE_ORDER_TOTAL_GV_INC_TAX = false`
- `MODULE_ORDER_TOTAL_GV_INC_SHIPPING = true`
- `MODULE_ORDER_TOTAL_GV_CALC_TAX = None`
- `DISPLAY_PRICE_WITH_TAX = false`
- flat shipping tax off
- item shipping tax off
- `DEFAULT_CURRENCY = USD`

## Direct `ot_gv` Coverage

Primary direct coverage lives in:

- [GiftVoucherRedeemReadOnlyTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemReadOnlyTest.php:1)
- [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:1)
- [OtGvShippingDetailsTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/Unit/testsSundry/OtGvShippingDetailsTest.php:1)

### Read-only / Access Behavior

`testGvRedeemGuestNoGVNum`

- File: [GiftVoucherRedeemReadOnlyTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemReadOnlyTest.php:18)
- Flags: no calculation flags changed
- Purpose: guest access to GV redemption page redirects to login
- Calculation detail: none; this covers route and access gating only

### Redemption And Order-Total Calculation Behavior

`testPurchaseWithGiftVoucher`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:84)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = true`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Eligible base includes merchandise, shipping, and tax.
  - Requested voucher amount exceeds order total, so `calculate_credit()` caps the deduction to the full order total.
  - With the shared fixture, that full eligible amount is `45.29`.
  - Result validated as full coverage with order total reduced to `0.00`.

`testPurchaseWithGiftVoucherExcludesTaxByDefault`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:129)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = true`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Eligible base = order total minus full order tax.
  - Arithmetic: `45.29 - 2.80 = 42.49`.
  - Shipping remains voucher-eligible.
  - Expected GV line `-$42.49`, leaving `2.80` tax unpaid.

`testPurchaseWithGiftVoucherExcludesShippingWhenConfigured`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:143)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Eligible base = order total minus shipping amount.
  - Arithmetic: `45.29 - 2.50 = 42.79`.
  - Tax remains voucher-eligible.
  - Expected GV line `-$42.79`, leaving `2.50` shipping unpaid.

`testPurchaseWithGiftVoucherExcludesShippingTaxWhenShippingIsTaxable`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:157)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
  - item shipping tax on
- Calculation path:
  - Shipping amount and shipping tax must both be excluded from the eligible base.
  - Arithmetic: `45.54 - 2.50 - 0.25 = 42.79`.
  - Because display is tax-exclusive, shipping amount and shipping tax are separate numbers.
  - Expected GV line `-$42.79`, leaving `2.75` shipping unpaid.

`testPurchaseWithGiftVoucherExcludesShippingTaxWhenShippingIsTaxableAndDisplayIsTaxInclusive`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:172)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = true`
  - item shipping tax on
- Calculation path:
  - Shipping display amount already contains shipping tax.
  - Arithmetic: `45.54 - 2.75 = 42.79`.
  - Excluding shipping should subtract taxable shipping exactly once.
  - Expected GV line `-$42.79`, leaving `2.75` unpaid.
- Regression note:
  - This test previously exposed a tax-inclusive shipping exclusion bug.
  - It now passes after correcting the shipping exclusion base in `get_order_total()`.

`testPurchaseWithGiftVoucherExcludesTaxAndTaxableShippingWhenDisplayIsTaxInclusive`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:188)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = true`
  - item shipping tax on
- Calculation path:
  - First exclude order tax from the eligible base.
  - Arithmetic step 1: `45.54 - 3.05 = 42.49`.
  - Then exclude shipping from the eligible base without subtracting shipping tax a second time.
  - Arithmetic step 2: `42.49 - 2.50 = 39.99`.
  - Expected GV line `-$39.99`, leaving `5.55` unpaid.
- Regression note:
  - This test previously exposed the tax-inclusive `include_tax=false` shipping exclusion bug.
  - It now passes with the corrected shipping ex-tax base calculation.

`testPurchaseWithGiftVoucherExcludesTaxAndShippingWhenConfigured`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:204)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Eligible base = merchandise subtotal only.
  - Arithmetic: `45.29 - 2.80 - 2.50 = 39.99`.
  - Expected GV line `-$39.99`, leaving shipping plus tax unpaid as `5.30`.

`testPurchaseWithGiftVoucherStandardTaxRecalculationUsesExcludedTaxBase`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:218)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = true`
  - `CALC_TAX = Standard`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Eligible base excludes tax.
  - Arithmetic base: `45.29 - 2.80 = 42.49`.
  - `calculate_deductions()` computes `ratio = voucher_deduction / eligible_base`.
  - Each tax group is reduced by that ratio.
  - Expected remaining total `34.63`.

`testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTax`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:233)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = Standard`
  - `DISPLAY_PRICE_WITH_TAX = false`
  - item shipping tax on
- Calculation path:
  - Shipping is excluded, so shipping tax must also be removed from `tax_groups` before standard tax proration.
  - Arithmetic base: `45.54 - 2.50 - 0.25 = 42.79`.
  - Voucher should not prorate against excluded shipping tax.
  - Expected remaining total `34.89`.

`testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTaxWhenDisplayIsTaxInclusive`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:249)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = Standard`
  - `DISPLAY_PRICE_WITH_TAX = true`
  - item shipping tax on
- Calculation path:
  - Same principle as the previous test, but now shipping display already contains shipping tax.
  - Arithmetic base: `45.54 - 2.75 = 42.79`.
  - Total-base adjustment and `tax_groups` adjustment must be handled independently.
  - Expected remaining total `34.89`.
- Regression note:
  - This test previously exposed an off-by-one-cent standard tax recalculation error in the tax-inclusive excluded-shipping path.
  - It now passes with the corrected shipping-tax removal from `tax_groups`.

`testPurchaseWithGiftVoucherExcludesFlatShippingTaxWhenDisplayIsTaxInclusive`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:266)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = true`
  - flat shipping tax on
  - shipping selection forced to `flat_flat`
- Calculation path:
  - Flat shipping display amount already contains shipping tax.
  - Arithmetic: `48.29 - 5.50 = 42.79`.
  - Excluding flat shipping should subtract the full taxable flat-shipping contribution exactly once.
  - Expected GV line `-$42.79`, leaving `5.50` unpaid.

`testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedFlatShippingTaxWhenDisplayIsTaxInclusive`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:282)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `CALC_TAX = Standard`
  - `DISPLAY_PRICE_WITH_TAX = true`
  - flat shipping tax on
  - shipping selection forced to `flat_flat`
- Calculation path:
  - Eligible base is the tax-inclusive merchandise amount only.
  - Arithmetic base: `48.29 - 5.50 = 42.79`.
  - Shipping tax must still be removed from `tax_groups` before proration.
  - Expected remaining total `37.64`.

### Direct Helper Fallback Coverage

`testGetOrderTotalRecomputesShippingTaxDetailsWhenSessionDescriptionIsMissing`

- File: [OtGvShippingDetailsTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/Unit/testsSundry/OtGvShippingDetailsTest.php:1)
- Flags:
  - `INC_TAX = true`
  - `INC_SHIPPING = false`
  - `DISPLAY_PRICE_WITH_TAX = false`
  - shipping selection mocked as `flat_flat`
  - `$_SESSION['shipping_tax_description']` intentionally unset
- Calculation path:
  - Forces `get_shipping_tax_details()` to bypass the normal session-backed description path.
  - Recomputes shipping tax details from the selected shipping module and zone data.
  - Verifies recomputed tax details of `0.50` and `SHIPPING TAX 10%`.
  - Verifies `get_order_total()` then excludes taxable flat shipping correctly: `48.29 - 5.00 - 0.50 = 42.79`.
  - Verifies excluded shipping tax is removed from `tax_groups` while merchandise tax remains intact.

### Coupon / Redemption Validation Behavior

`testSubmittingRedeemWithoutVoucherCodeShowsErrorWithoutLogs`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:266)
- Flags: defaults
- Purpose: missing redemption code surfaces validation message
- Calculation detail: order totals remain unchanged at baseline `45.29`

`testSubmittingInvalidRedeemCodeShowsErrorWithoutLogs`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:281)
- Flags: defaults
- Purpose: nonexistent redemption code is rejected cleanly
- Calculation detail: order totals remain unchanged at baseline `45.29`

`testSubmittingAlreadyRedeemedVoucherCodeShowsErrorWithoutLogs`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:296)
- Flags: defaults
- Purpose: previously redeemed code is rejected
- Calculation detail: order totals remain unchanged at baseline `45.29`

### Coverage For Historical Full-Coverage Edge Cases

`testPurchaseCreditCoversFails`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:323)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = true`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Voucher amount is set just below full coverage threshold.
  - Arithmetic: requested `45.28` against a `45.29` eligible base.
  - Confirms checkout still requires a payment method instead of treating the order as fully covered.

`testPurchaseCreditCoversFailsShippingTax`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:338)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = true`
  - `CALC_TAX = None`
  - `DISPLAY_PRICE_WITH_TAX = false`
  - flat shipping tax on
- Calculation path:
  - Same threshold behavior, but with shipping tax present.
  - Arithmetic: requested `45.28` against a `45.29` eligible base while flat shipping tax is enabled elsewhere in the stack.
  - Confirms shipping-tax presence does not incorrectly mark the order as fully paid.

### Credit-Note Tax Recalculation

`testPurchaseWithGiftVoucherCreditNoteTaxRecalculationAdjustsTaxLine`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:355)
- Flags:
  - `INC_TAX = false`
  - `INC_SHIPPING = true`
  - `CALC_TAX = Credit Note`
  - `MODULE_ORDER_TOTAL_GV_TAX_CLASS = 1`
  - `DISPLAY_PRICE_WITH_TAX = false`
- Calculation path:
  - Voucher deduction is calculated against the eligible base.
  - Arithmetic base: `42.49`, deduction requested: `10.00`.
  - Tax adjustment is then recalculated using the configured GV tax class rather than tax-group proration.
  - Expected remaining total `35.29`.

### Persistence And Currency Handling

`testPartialGiftVoucherApplicationPersistsReducedBalanceAfterOrder`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:371)
- Flags: defaults
- Calculation path:
  - Applies a partial voucher of `10.00`.
  - Arithmetic: `45.29 - 10.00 = 35.29` displayed, with persisted order total `35.2893` before formatting.
  - Confirms order total persistence and post-order GV balance decrement.
  - Expected order total persisted as `35.2893`.
  - Expected customer GV balance persisted as `990.0000`.

`testPurchaseWithGiftVoucherSEK`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:418)
- Flags:
  - defaults, plus `DEFAULT_CURRENCY = SEK`
- Calculation path:
  - Confirms localized numeric input `1,5` is normalized and applied correctly.
  - Expected remaining total display `SEK6,4259`.

`testSendGiftVoucherSEK`

- File: [GiftVoucherRedeemTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php:436)
- Flags:
  - defaults, plus `DEFAULT_CURRENCY = SEK`
- Calculation path:
  - Covers outbound GV send flow rather than checkout deduction.
  - Confirms localized amount parsing, coupon amount storage, email tracking, and balance decrement.

## Indirect `ot_gv` Integration Coverage

These tests are not dedicated `ot_gv` unit-style scenarios, but they do exercise gift-voucher redemption inside another total-calculation flow:

- [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:1)

The low-order-fee tests matter because `ot_gv` participates in the combined order-total stack, not in isolation.

`it_test_a_loworderfee_with_almost_full_GV`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:35)
- GV interaction:
  - applies `cot_gv = 45.28`
  - confirms order is still not fully covered once low-order fee is present

`it_tests_lowOrderFee_with_full_GV`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:61)
- GV interaction:
  - applies `cot_gv = 45.29`
  - confirms order still requires payment when low-order fee is present

`it_tests_loworderfee_with_almost_full_GV_and_shippingTax`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:87)
- GV interaction:
  - shipping tax on
  - applies `cot_gv = 45.76`
  - confirms threshold behavior with low-order fee plus shipping tax

`it_tests_loworderfee_with_full_GV_and_shipping_tax`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:116)
- GV interaction:
  - shipping tax on
  - applies `cot_gv = 50.54`
  - confirms a fully covered order after shipping tax and low-order fee are included

`it_tests_loworderfee_with_full_GV_shipping_tax_inclusive`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:151)
- GV interaction:
  - `DISPLAY_PRICE_WITH_TAX = true`
  - shipping tax on
  - applies `cot_gv = 50.54`
  - confirms full-coverage behavior in a tax-inclusive storefront

`it_tests_loworderfee_with_group_discount_and_insufficient_GV`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:198)
- GV interaction:
  - applies `cot_gv = 39.99`
  - confirms voucher does not fully cover a discounted order that still includes low-order fee

`it_tests_loworderfee_with_group_discount_and_full_GV`

- File: [LowOrderFeeTest.php](/home/wilt/Projects/zencart/not_for_release/testFramework/FeatureStore/LowOrderFees/LowOrderFeeTest.php:224)
- GV interaction:
  - applies `cot_gv = 46.01`
  - confirms full-GV behavior when group discount and low-order fee are both in play

Verification status:

- `ddev composer exec phpunit -- --filter 'GiftVoucherRedeemTest::testPurchaseWithGiftVoucherExcludesShippingTaxWhenShippingIsTaxableAndDisplayIsTaxInclusive|GiftVoucherRedeemTest::testPurchaseWithGiftVoucherExcludesTaxAndTaxableShippingWhenDisplayIsTaxInclusive|GiftVoucherRedeemTest::testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTaxWhenDisplayIsTaxInclusive|GiftVoucherRedeemTest::testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedShippingTax' not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php`
  - result: `OK (4 tests, 140 assertions)`
- `ddev composer exec phpunit -- not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php`
  - result: `OK (20 tests, 700 assertions)`
- `ddev composer exec phpunit -- --filter 'GiftVoucherRedeemTest::testPurchaseWithGiftVoucherExcludesFlatShippingTaxWhenDisplayIsTaxInclusive|GiftVoucherRedeemTest::testPurchaseWithGiftVoucherStandardTaxDoesNotRecalculateExcludedFlatShippingTaxWhenDisplayIsTaxInclusive|OtGvShippingDetailsTest' not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php not_for_release/testFramework/Unit/testsSundry/OtGvShippingDetailsTest.php`
  - result: `OK (3 tests, 75 assertions)`
- `ddev composer exec phpunit -- not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php not_for_release/testFramework/Unit/testsSundry/OtGvShippingDetailsTest.php`
  - result: `OK (23 tests, 775 assertions)`

## Suggested Targeted Test Command

To run the dedicated storefront GV suite with the project’s preferred test wrapper:

```bash
ddev composer exec phpunit -- not_for_release/testFramework/FeatureStore/GVCoupons/GiftVoucherRedeemTest.php
```
