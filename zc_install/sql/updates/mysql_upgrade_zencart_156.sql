#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.5 to v1.5.6
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2018 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Author: DrByte  New in v1.5.6 $
#

############ IMPORTANT INSTRUCTIONS ###############
#
# * Zen Cart uses the zc_install/index.php program to do database upgrades
# * This SQL script is intended to be used by running zc_install
# * It is *not* recommended to simply run these statements manually via any other means
# * ie: not via phpMyAdmin or via the Install SQL Patch tool in Zen Cart admin
# * The zc_install program catches possible problems and also handles table-prefixes automatically
# *
# * To use the zc_install program to do your database upgrade:
# * a. Upload the NEWEST zc_install folder to your server
# * b. Surf to zc_install/index.php via your browser
# * c. On the System Inspection page, scroll to the bottom and click on Database Upgrade
# *    NOTE: do NOT click on the "Install" button, because that will erase your database.
# * d. On the Database Upgrade screen, you will be presented with a list of checkboxes for
# *    various Zen Cart versions, with the recommended upgrades already pre-selected.
# * e. Verify the checkboxes, then scroll down and enter your Zen Cart Admin username
# *    and password, and then click on the Upgrade button.
# * f. If any errors occur, you will be notified.  Some warnings can be ignored.
# * g. When done, you will be taken to the Finished page.
#
#####################################################

# Set store to Down-For-Maintenance mode.  Must reset manually via admin after upgrade is done.
#UPDATE configuration set configuration_value = 'true' where configuration_key = 'DOWN_FOR_MAINTENANCE';

# Clear out active customer sessions
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;

# Re-repair things that some rogue plugins mistakenly damage:
UPDATE configuration set configuration_group_id = 6 where configuration_key in ('PRODUCTS_OPTIONS_TYPE_SELECT', 'UPLOAD_PREFIX', 'TEXT_PREFIX');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Product option type Select', 'PRODUCTS_OPTIONS_TYPE_SELECT', '0', 'The number representing the Select type of product option.', 6, NULL, now(), now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Upload prefix', 'UPLOAD_PREFIX', 'upload_', 'Prefix used to differentiate between upload options and other options', 6, NULL, now(), now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Text prefix', 'TEXT_PREFIX', 'txt_', 'Prefix used to differentiate between text option values and other option values', 6, NULL, now(), now(), NULL, NULL);

# New Values
UPDATE configuration SET configuration_description =  'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br /><strong>SMTPAUTH</strong> should be used by most sites!, as it provides secure sending of authenticated email. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>Gmail</strong> is used for sending emails using Google\'s mail service, and requires the [less secure] setting enabled in your gmail account.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to use sendmail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br />MOST SITES WILL USE [SMTPAUTH].', set_function = 'zen_cfg_select_option(array(\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Gmail\'),' WHERE configuration_key = 'EMAIL_TRANSPORT';

# Updates
ALTER TABLE products_options MODIFY products_options_comment varchar(256) default NULL;
ALTER TABLE configuration ADD val_function text default NULL AFTER set_function;

# allow longer image paths
ALTER TABLE products MODIFY products_image varchar(255) default NULL;
ALTER TABLE products_attributes MODIFY attributes_image varchar(255) default NULL;
ALTER TABLE banners MODIFY banners_image varchar(255) NOT NULL default '';
ALTER TABLE categories MODIFY categories_image varchar(255) default NULL;
ALTER TABLE manufacturers MODIFY manufacturers_image varchar(255) default NULL;
ALTER TABLE record_artists MODIFY artists_image varchar(255) default NULL;
ALTER TABLE record_company MODIFY record_company_image varchar(255) default NULL;

ALTER TABLE coupons ADD coupon_calc_base TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE coupons ADD coupon_order_limit INT( 4 ) NOT NULL DEFAULT 0;
ALTER TABLE coupons ADD coupon_is_valid_for_sales TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE coupons ADD coupon_product_count TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE coupons_description MODIFY coupon_name VARCHAR(64) NOT NULL DEFAULT '';

# Add fields for easier order reconstruction/edit
ALTER TABLE orders ADD order_weight FLOAT default NULL;
ALTER TABLE orders MODIFY shipping_method VARCHAR(255) DEFAULT NULL;
ALTER TABLE orders MODIFY order_total decimal(15,4) default NULL;
ALTER TABLE orders MODIFY order_tax decimal(15,4) default NULL;

ALTER TABLE orders_products ADD products_weight float default NULL;
ALTER TABLE orders_products ADD products_virtual tinyint( 1 ) default NULL;
ALTER TABLE orders_products ADD product_is_always_free_shipping tinyint( 1 ) default NULL;
ALTER TABLE orders_products ADD products_quantity_order_min float default NULL;
ALTER TABLE orders_products ADD products_quantity_order_units float default NULL;
ALTER TABLE orders_products ADD products_quantity_order_max float default NULL;
ALTER TABLE orders_products ADD products_quantity_mixed tinyint( 1 ) default NULL;
ALTER TABLE orders_products ADD products_mixed_discount_quantity tinyint( 1 ) default NULL;
ALTER TABLE orders_products_download ADD products_attributes_id int( 11 ) default NULL;

# Clean up expired prids from baskets
DELETE FROM customers_basket WHERE CAST(products_id AS unsigned) NOT IN (
SELECT products_id
FROM products WHERE products_status > 0);
DELETE FROM customers_basket_attributes WHERE CAST(products_id AS unsigned) NOT IN (
SELECT products_id
FROM products WHERE products_status > 0);

# Clean up missing relations for deleted products
DELETE FROM specials WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_to_categories WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_description WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM meta_tags_products_description WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_attributes WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM reviews WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM reviews_description WHERE reviews_id NOT IN (
SELECT reviews_id
FROM reviews);
DELETE FROM featured WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_discount_quantity WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM coupon_restrict WHERE product_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_notifications WHERE products_id NOT IN (
SELECT products_id
FROM products);
DELETE FROM products_attributes_download WHERE products_attributes_id IN (
SELECT products_attributes_id FROM products_attributes WHERE products_id NOT IN (
SELECT products_id
FROM products));

## alter admin_pages for new product listing pages
UPDATE admin_pages
SET language_key = 'BOX_CATALOG_CATEGORY',
    main_page = 'FILENAME_CATEGORY_PRODUCT_LISTING',
    display_on_menu = 'N',
    sort_order = 18
WHERE page_key = 'categories';

INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('categoriesProductListing', 'BOX_CATALOG_CATEGORIES_PRODUCTS', 'FILENAME_CATEGORY_PRODUCT_LISTING', '', 'catalog', 'Y', 1);

DELETE FROM admin_pages WHERE page_key = 'linkpointReview';

ALTER TABLE customers_basket DROP final_price;

## add support for multi lingualez pages
CREATE TABLE IF NOT EXISTS ezpages_content (
  pages_id int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL DEFAULT '1',
  pages_title varchar(64) NOT NULL DEFAULT '',
  pages_html_text text NOT NULL,
  UNIQUE KEY ez_pages (pages_id, languages_id),
  KEY idx_lang_id_zen (languages_id)
) ENGINE=MyISAM;

INSERT IGNORE INTO ezpages_content (pages_id, languages_id, pages_title, pages_html_text)
SELECT e.pages_id, l.languages_id, e.pages_title, e.pages_html_text
FROM ezpages e
LEFT JOIN languages l
ON 1;

ALTER TABLE ezpages
  DROP languages_id,
  DROP pages_title,
  DROP pages_html_text;

## support for utf8mb4 index limitations in MySQL 5.5-5.6
ALTER TABLE admin_menus MODIFY menu_key VARCHAR(191) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY menu_key varchar(191) NOT NULL default '';
ALTER TABLE admin_pages MODIFY page_key VARCHAR(191) NOT NULL DEFAULT '';
ALTER TABLE admin_pages_to_profiles MODIFY page_key varchar(191) NOT NULL default '';
ALTER TABLE get_terms_to_filter MODIFY get_term_name varchar(191) NOT NULL default '';
ALTER TABLE configuration MODIFY configuration_key varchar(180) NOT NULL default '';
ALTER TABLE product_type_layout MODIFY configuration_key varchar(180) NOT NULL default '';
ALTER TABLE whos_online DROP KEY idx_last_page_url_zen;
ALTER TABLE whos_online ADD KEY idx_last_page_url_zen (last_page_url(191));
ALTER TABLE media_manager DROP KEY idx_media_name_zen;
ALTER TABLE media_manager ADD KEY idx_media_name_zen (media_name(191));
# truncate was done earlier in this file already, but if copy/pasting for some reason, do the truncate below, to cleanup the table
#TRUNCATE TABLE whos_online;
ALTER TABLE whos_online MODIFY session_id varchar(191) NOT NULL default '';
# recreating sessions table since its storage engine is changing to InnoDB:
DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  sesskey varchar(191) NOT NULL default '',
  expiry int(11) unsigned NOT NULL default 0,
  value mediumblob NOT NULL,
  PRIMARY KEY  (sesskey)
) ENGINE=InnoDB;






#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.6-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.5->1.5.6-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.6', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.5->1.5.6', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT
