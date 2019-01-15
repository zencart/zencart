![Zen Cart](https://www.zen-cart.com/docs/logo.gif)

[Documentation Home](./index.html) 

# Welcome to Zen Cart

Zen Cart is made available to you for your use, addition, changes, modification, etc. without charge, under Version 2 of the GNU General Public License.

While we do not charge for this software, donations are greatly appreciated, each time you install a new version, to help cover the expenses of maintenance, upgrades, updates, the free support forum and the continued development of this software for your online E-Commerce store.

Donations can be made on the [Zen Cart Team Page](https://www.zen-cart.com/donate)

We appreciate your support.  
_The Zen Cart Team_

# About PHP versions

Zen Cart v2.0 is compatible with PHP 5.6 to 7.3, with MySQL 5.1 to 5.7 (or MariaDB 10.0-10.3) and Apache 2.2/2.4

While it is compatible with PHP 5.4.9 through PHP 7.3 for backward-compatibility while upgrading your site, you should be using a newer version whenever possible. As of the time of writing this, [PHP 5.6 is considered obsolete.](http://php.net/supported-versions.php) and you should be using _PHP 7.1 or greater_.

# Upgrade Instructions

The [standard complete site upgrade](http://www.zen-cart.com/upgrades) instructions apply for upgrading to Zen Cart v2.0 from any previous version.

# CHANGELOG - List of Changed Files

For a list of files that have been changed since v1.5.6, see the [changed_files-v2-0.html](changed_files-v2-0.html) document.

# Whats New ... Changes from v1.5.6 to v2.0

## Improvements and Fixes since v1.5.6

### Improvements:

*   Infrastructure
    *   Comprehensive Unit Test framework
        1.  Many functions are now automatically tested at build with with phpUnit.
    *   Separation of vendor-provided files
*   Installation and Setup:
    *   Streamlined Configuration
        1.  All site settings now only in /includes/configure.php.
        2.  The /admin/includes/configure.php file is no longer required.
*   Guest Checkout:
    *   Guest checkout is now natively available in Zen Cart.
        1.  No mods required.
        2.  Can be disabled by storeowner.
*   Standard Checkout:
    *   Product Shipping Insurance Support.
        1.  For shipping carriers that offer this in their modules.
    *   Improvements to Checkout Flows.
        1.  Passwords are not required until end of checkout.
        2.  Optional abbreviated checkout for virtual+free purchases.
*   Order Processing:
    *   Order Weight tracking.
        1.  Total order weight and per product weight recorded at checkout time.
*   Languages:
    *   Multilingual Configuration Menu for Country Names.
    *   Remembers Customer's Order Language.
        1.  Order updates automaitically sent in saved language.
    *   Language files simplified to relocate locale-specific content into a new locale.php file.
        1.  Most sites will now touch fewer files for customization.
*   Product Features:
    *   Product Microdata Markup.
    *   New Product Indicators.
        1.  Product stock availability and product condition indicators.
    *   Single Attribute Products Improvements.
        1.  Can now be added to cart from product listing.
        2.  Instead of via "more info" taking you to the product page first
    *   Externally Hosted Downloadable Virtual Products.
        1.  Storable on AWS S3, Dropbox and any other service with an available plugin.
*   Admin Improvements:
    *   Date Selection Improvements.
        1.  Spiffycal replaced with powerful jQuery plugin.
    *   Reporting Tools
        1.  Duplicate model reporting.
        2.  System inspection to report on database changes from a base install.
    *   Flexible Admin Templating System.
        1.  Allows restyling to suit theme preferences.
    *   Admin Home Page Dashboard Widgets.
        1.  Displays metrics such as:
            1.  current customer activity
            2.  banner imprint graphs
            3.  sales history graphs
            4.  ... and more.
*   Promotional Tools:
    *   Allow or Disallow Gift Certificates to be put on "Special".
    *   Coupon Capability Improvements.
        1.  Combine free shipping with amount or percent discount
        2.  Support rules such as:
            1.  combining with sales
            2.  minimum order amount
            3.  limit by number of orders
            4.  export option
            5.  ... and more.
*   Designers will Love:
    *   HTML 5 Codebase.
        1.  The codebase has been modernised to leverage HTML 5 features
    *   No Need for Custom Graphics.
        1.  Use CSS buttons and font icons in place of custom graphics.
    *   Flexibile CSS Framework Adaption.
        1.  Templating system allows for adoption of CSS frameworks such as Bootstrap.
    *   Painless Minification of CSS, JS and More.
        1.  Template hooks provided to trigger minification of CSS, JS and more.
    *   Flexibile CSS and JS Placement.
        1.  Increased flexibility for custom "per page" CSS and JS placement on pages.
    *   Powerful New Template "Middle Tier".
        1.  Allows for "shared" customizations which might apply to multiple templates.
*   Developers will Love:
    *   Multi Tenancy Capability.
    *   Nginx Configuration Template.
        1.  Nginx directives equivalent to Apache htaccess rules to secure Zen Cart are provided after installation to serve as a start point.
    *   Cart Content Inspector Functions.
        1.  Allows custom actions to be built around cart contents.
        2.  Includes reacting to product weight, category, value, ... and more.
    *   Additional Notifier Hooks.
        1.  Allows for customizing category tabs, reviews, cart contents, downloads, product images and popups.
    *   In-depth Code Modernization.
        1.  Progressively incorporating namespaced "OOP" rewrites of various segments.
    *   Function Consolidation.
        1.  Previously duplicated functions, across admin and catalog, are now shared from one instance.Documentation for developers on features of v2.0 is available on the Zen Cart [Developer Documentation Page](http://docs.zen-cart.com/Developer_Documentation/intro).

# Help and Support

For additional help and support, visit the [Zen Cart FAQ](https://www.zen-cart.com/tutorials) and the [Zen Cart Support Forum](https://www.zen-cart.com/forum.php).

Zen Cart is derived from: Copyright 2003 osCommerce  

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;  
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE  
and is redistributable under Version 2 of the GNU General Public License.

![O S I Certified](https://www.zen-cart.com/docs/osi-certified-120x100.png)  
This software is OSI Certified Open Source Software.  
OSI Certified is a certification mark of the Open Source Initiative.

Copyright 2003 - 2019 Zen Ventures, LLC  

Zen Cart&reg;
[www.zen-cart.com](https://www.zen-cart.com)

