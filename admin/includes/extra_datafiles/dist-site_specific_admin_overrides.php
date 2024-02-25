<?php
/**
 * A collection of site-specific overrides for the admin operation.
 *
 * There are some features in the base Zen Cart processing that can be overridden for a specific
 * site, as identified in this module.
 *
 * For use on YOUR site, make a copy of this file (which has all entries commented-out) to /admin/includes/extra_datafiles/site_specific_admin_overrides.php
 * and make your edits there.  Otherwise, your overrides might get "lost" on a future Zen Cart upgrade.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jan 29 Modified in v2.0.0-beta1 $
 */
// -----
// The flags to indicate if attribute images should be displayed in invoice and packing slip.
// Used in: admin/invoice.php, admin/packingslip.php 
//
// $show_attrib_images   $show_attrib_images_pack
// true or unset         true or unset            ...... attribute images are shown on invoice and packingslip (the default).
// false                 false or unset           ...... attribute images are NOT shown on invoice or packingslip.
// false                 true                     ...... attribute images are NOT shown on invoice but are shown packingslip.
// true or unset         false                    ...... attribute images are shown on invoice but are NOT shown packingslip.
//$show_attrib_images = true;
//$show_attrib_images_pack = true;

// Set the width of the attribute image used in packingslip and invoice.
// Used in: admin/invoice.php, admin/packingslip.php
// 
// The default is 25. if $show_attrinb_images = false is set above then setting this value will have no effect
// Change 25 below to the number of px you require. Do NOT remove the quotes!
//$attr_img_width = '25';

// The flags to indicate if product images should be displayed in invoice and packing slip.
// Used in: admin/invoice.php, admin/packingslip.php 
// 
// $show_product_images  $show_product_images_pack
// true or unset         true or unset            ...... product images are shown on invoice and packingslip (the default).
// false                 false or unset           ...... product images are NOT shown on invoice or packingslip.
// false                 true                     ...... product images are NOT shown on invoice but are shown on packingslip.
// true or unset         false                    ...... product images are shown on invoice and but are NOT shown on packingslip.
//$show_product_images = true;
//$show_product_images_pack = true;

// Flag to indicate if the product tax is displayed on the order details screen and invoice
// Used in: admin/invoice.php, admin/orders.php
//
// true ..... Display the products tax amounts (the default).
// false .... The products Tax amount is NOT displayed.
//$show_product_tax = true;

// Indicate if the downloads manager page should show the file date.
// Used in: admin/downloads_manager.php
//
// true ...... Show the file date.
// false ..... Do NOT show the file date (the default).
//$show_download_date = false;

// Flag to identify if attribute details should be displayed in the popup from the new orders dashboard widget.
// Used in: admin/includes/modules/RecentOrdersDashboardWidget.php
//
// true ...... Attributes are displayed in the popup (the default).
// false ..... Attributes are NOT displayed in the popup.
//$includeAttributesInPopoverRows = true;

// Maximum number of rows to display in the new orders section of the dashboard widget
// Used in: admin/includes/modules/RecentOrdersDashboardWidget.php
//
// Change 25 below to the number of rows you require.
//$recentOrdersMaxRows = 25;

//Flag to indicate if the Quick view popup should be displayed on the order page.
// Used in: admin/orders.php
//
// true ..... Quick view popup icon is displayed.
// false .... Quick view popup icon is NOT displayed (the default).
//$quick_view_popover_enabled = false;

// Flag to indicate if the attributes info is displayed in Order Quick view popup.
// Has no effect if $quick_view_popover_enabled = false;
// Used in: admin/orders.php
//
// true ...... Attributes are displayed in the popup (the default).
// false ..... Attributes are NOT displayed in the popup.
//$includeAttributesInProductDetailRows = true;

// Flag to indicate id the Zone information is to be displayed on the Order screen.
// Used in: admin/orders.php
// 
// true ..... Display the zone information column (the default).
// false .... Do NOT display the zone information column.
//$show_zone_info = true;

// Flag to indicate that the FontAwesome v4 shim CSS file should NOT be loaded
// in the head of every admin page, to make obsolete FontAwesome icon names
// like fa-star-o work.
// Useful when no addons using FontAwesome v4 are deployed.
// Used in: admin/includes/admin_html_head.php
//
// true ..... no link will be created.
// false .... a <link> element will load the v4-shims.min.css file.
//$disableFontAwesomeV4Compatibility = true;

// Display the registration IP address in the customer listing
// Used in: admin/includes/customers.php
//
// true ..... show IP address
// false .... hide IP address (default)
// $show_registration_ip_in_listing = false;

// Display an order's overall weight and the weight of each product?  Used
// Used in: admin/orders.php
//
// Note: Orders placed on Zen Cart versions prior to 1.5.6 did not record
// the order's overall weight.  If an order's overall weight isn't recorded,
// this setting will automatically be set to (bool)false for that order.
//
// true .... (default) Show the overall and product-specific weights, if present.
// false ... Don't show the weights
// $show_orders_weights = true;


//
//Developer Debugging flags
//

// Identify whether the zcDate class' (added in Zen Cart 1.5.8) debug-output is initially enabled.
// Used in: includes/zcDate.php
//
// true ...... The zcDate debug is enabled.
// false ..... The zcDate debug is disabled (the default).
//$zen_date_debug = false;
