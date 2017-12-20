#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.5 to v1.5.6
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2017 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Author: DrByte  August 2017 -0500 New in v1.5.6 $
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


# Updates
ALTER TABLE products_options MODIFY products_options_comment varchar(256) default NULL;

ALTER TABLE coupons ADD coupon_calc_base TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE coupons ADD coupon_order_limit INT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE coupons ADD coupon_is_valid_for_sales TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE coupons ADD coupon_product_count TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE coupons_description MODIFY coupon_name VARCHAR(64) NOT NULL DEFAULT '';

# Add fields for easier order reconstruction/edit
ALTER TABLE orders ADD order_weight FLOAT NOT NULL DEFAULT '0';
ALTER TABLE orders MODIFY shipping_method VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE orders MODIFY order_total decimal(15,4) default NULL;
ALTER TABLE orders MODIFY order_tax decimal(15,4) default NULL;

ALTER TABLE orders_products ADD products_weight float NOT NULL default '0';
ALTER TABLE orders_products ADD products_virtual tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD product_is_always_free_shipping tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD products_quantity_order_min float NOT NULL default '1';
ALTER TABLE orders_products ADD products_quantity_order_units float NOT NULL default '1';
ALTER TABLE orders_products ADD products_quantity_order_max float NOT NULL default '0';
ALTER TABLE orders_products ADD products_quantity_mixed tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD products_mixed_discount_quantity tinyint( 1 ) NOT NULL default '1';
ALTER TABLE orders_products_download ADD products_attributes_id int( 11 ) NOT NULL default '0';

# Clean up expired prids from baskets
DELETE FROM customers_basket WHERE CAST(products_id AS unsigned) not IN (
SELECT products_id 
FROM products WHERE products_status > 0);
DELETE FROM customers_basket_attributes WHERE CAST(products_id AS unsigned) not IN (
SELECT products_id 
FROM products WHERE products_status > 0);


DELETE FROM admin_pages WHERE page_key = 'linkpointReview';
ALTER TABLE customers_basket DROP final_price;

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

