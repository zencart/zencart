---
name: checkout
description: Map of the Zen Cart storefront cart and checkout flow, which spans page modules, the payment/shipping/order-total module directories, the order and shopping_cart classes, coupon and gift-voucher code, and payment-module listeners at the repo root. Use for any task touching the shopping cart, checkout pages, order creation, payment or shipping modules, order totals, discounts, coupons, gift vouchers.
---

# Checkout and cart: where the pieces live

This feature is spread across several directories, so a path-scoped rule cannot cover it.
Start from this map, then read the files the task actually touches.

## Pages (`includes/modules/pages/`)

`shopping_cart/`, `checkout_shipping/`, `checkout_shipping_address/`, `checkout_payment/`,
`checkout_payment_address/`, `checkout_confirmation/`, `checkout_process/`, `checkout_success/`.
Each has `header_php.php` (logic) and a `tpl_<page>.php` template under
`includes/templates/template_default/templates/`.

Shared page modules in `includes/modules/`: `checkout_process.php`, `checkout_address_book.php`,
`checkout_new_address.php`, `shipping_estimator.php`.

## Classes (`includes/classes/`)

- `shopping_cart.php`: the session cart (contents, totals, attributes, restrictions).
- `order.php`: builds and stores the order; lowercase class name is an accepted legacy exception, do not rename.
- `payment.php`, `shipping.php`, `order_total.php`: load and iterate the installed modules from the directories below.
- `ZenShipping.php`, `Coupon.php`, `CouponValidation.php`.
- Functions: `includes/functions/functions_gvcoupons.php` (gift vouchers and coupons).

## Module directories

- `includes/modules/payment/`, `includes/modules/shipping/`, `includes/modules/order_total/`.
- A module class may carry `install()`, `remove()`, `keys()` to manage its own `configuration` rows; those are invoked from the admin Modules pages. Schema changes belong in a plugin installer, never in those methods.
- New payment/shipping/order-total modules should be encapsulated plugins (`.ai/rules/plugins.md`). `PayPalRestful` in `zc_plugins/` is the reference implementation.

## Listeners at the repo root

`ipn_main_handler.php`, `ppr_listener.php`, `ppr_webhook.php` are core PayPal-specific entry points
that bootstrap the app outside the normal page flow. Treat them as trust boundaries when reviewing.

## Things to check before changing behavior

- Settings such as `STOCK_CHECK`, `STOCK_ALLOW_CHECKOUT`, `MODULE_*` values are business logic: read them with `zen_config()`, never `$tplSetting` (`.ai/rules/templates.md`).
- Totals ordering and visibility are driven by each order-total module's configuration, not by code order.
- Feature tests for these flows live under `not_for_release/testFramework/FeatureStore/`; run the store suite (`.ai/rules/testing.md`) after changes.

