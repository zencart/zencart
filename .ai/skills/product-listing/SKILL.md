---
name: product-listing
description: Map of Zen Cart product and category display, which spans the index/product_info/specials/featured/new-products pages, the product_listing and listing_display_order modules, the Product and Category classes, the products/categories/prices/attributes function files, and the tpl_* listing templates. Use for any task touching product listings, category pages, product info pages, search results, prices, attributes, product images, or sorting and pagination of products.
---

# Product listing and display: where the pieces live

This feature spans several directories, so a path-scoped rule cannot cover it.
Start from this map, then read the files the task actually touches.

## Pages (`includes/modules/pages/`)

`index/` (category and product lists), `product_info/`, `products_new/`, `specials/`,
`featured_products/`, `featured_categories/`, `products_all/`, `advanced_search_result/`.

## Listing modules (`includes/modules/`)

`product_listing.php` (the core listing loop, column definitions, pagination),
`listing_display_order.php` and `product_listing_alpha_sorter.php` (sorting),
`new_products.php`, `featured_products.php`, `upcoming_products.php`,
`also_purchased_products.php`, `products_quantity_discounts.php`, `attributes.php`,
`main_product_image.php`, `product_prev_next.php`, `category_row.php`, `categories_tabs.php`,
`category_icon_display.php`, `featured_categories.php`.

## Classes and functions

- `includes/classes/Product.php`, `Category.php`, `ProductConfigurationSwitch.php`.
- Legacy, accepted exceptions: `category_tree.php` (lowercase class), `products.php` (deprecated; do not use in new code).
- `includes/functions/functions_products.php`, `functions_categories.php`, `functions_prices.php`, `functions_attributes.php`, `functions_product_images.php`. Several of these are reachable from admin as well as catalog; trace callers before changing signatures or relying on catalog-only globals.

## Templates (`includes/templates/template_default/templates/`)

`tpl_modules_product_listing.php`, `tpl_index_product_list.php`, `tpl_product_info_display.php`
and its `_details` / `_noproduct` / music variants, `tpl_modules_featured_products.php`,
`tpl_modules_listing_display_order.php`, `tpl_modules_main_product_image.php`,
`tpl_modules_products_quantity_discounts.php`, `tpl_modules_also_purchased_products.php`.
The active template may override any of these under `includes/templates/<template>/`.

## Things to check before changing behavior

- Display settings (columns, image sizes, show/hide sections) are template-presentation concerns and may be read through `$tplSetting`; stock, pricing, and `SHOW_*_ATTRIBUTES` (sourced from `product_type_layout`) require `zen_config()` (`.ai/rules/templates.md`).
- Product-type layouts change which template variant renders; check the product's type before assuming `tpl_product_info_display.php`.
- Unit tests for categories, images and template resolution live under `not_for_release/testFramework/Unit/testsCategories/`, `testsCatalogImages/`, `testsTemplateResolver/`, `testsHtmlOutput/`.
