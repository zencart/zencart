#
# * This SQL script upgrades the core Zen Cart database structure from v1.3.5 to v1.3.6
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2016 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
#

## CONFIGURATION TABLE
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Page-Not-Found Status', 'DEFINE_PAGE_NOT_FOUND_STATUS', '1', 'Enable the Defined Page-Not-Found Text from define-pages?<br />0= Define Text OFF<br />1= Define Text ON', '25', '67', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Currency Conversion Ratio', 'CURRENCY_UPLIFT_RATIO', '1.05', 'When auto-updating currencies, what "uplift" ratio should be used to calculate the exchange rate used by your store?<br />ie: the bank rate is obtained from the currency-exchange servers; how much extra do you want to charge in order to make up the difference between the bank rate and the consumer rate?<br /><br /><strong>Default: 1.05 </strong><br />This will cause the published bank rate to be multiplied by 1.05 to set the currency rates in your store.', 1, 55, NULL, now(), NULL, NULL);

UPDATE configuration SET sort_order = '19' where configuration_key = 'SHOW_COUNTS_ADMIN';
UPDATE configuration SET configuration_description = 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should only be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.', set_function = 'zen_cfg_select_option(array(\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Qmail\'),' where configuration_key = 'EMAIL_TRANSPORT';
UPDATE configuration SET configuration_group_id = '6' where configuration_key = 'ENTRY_EMAIL_ADDRESS_CHECK';
UPDATE configuration SET configuration_title = 'Display Product Add to Cart Button (0=off; 1=on; 2=on with Qty Box per Product)', configuration_description = 'Do you want to display the Add to Cart Button?<br /><br /><strong>NOTE:</strong> Turn OFF Display Multiple Products Qty Box Status to use Option 2 on with Qty Box per Product' WHERE configuration_key= 'PRODUCT_LIST_PRICE_BUY_NOW';
UPDATE configuration SET configuration_title = 'Previous Next - Navigation Includes Category Position' WHERE configuration_key= 'PRODUCT_INFO_CATEGORIES';
UPDATE configuration SET set_function='zen_cfg_pull_down_htmleditors(' WHERE configuration_key='HTML_EDITOR_PREFERENCE';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Top Subcategory Image Width', 'SUBCATEGORY_IMAGE_TOP_WIDTH', '150', 'The pixel width of Top subcategory images<br />Top subcategory is when the Category contains subcategories', '4', '15', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Top Subcategory Image Height', 'SUBCATEGORY_IMAGE_TOP_HEIGHT', '85', 'The pixel height of Top subcategory images<br />Top subcategory is when the Category contains subcategories', '4', '16', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show New Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_NEW_PRODUCTS', '1', 'Show New Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '30', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Featured Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_FEATURED_PRODUCTS', '2', 'Show Featured Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '31', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Special Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_SPECIALS_PRODUCTS', '3', 'Show Special Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '32', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Upcoming Products on empty Shopping Cart Page', 'SHOW_SHOPPING_CART_EMPTY_UPCOMING', '4', 'Show Upcoming Products on empty Shopping Cart Page<br />0= off or set the sort order', '9', '33', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show SubCategories on Main Page while navigating', 'PRODUCT_LIST_CATEGORY_ROW_STATUS', '1', 'Show Sub-Categories on Main Page while navigating through Categories<br /><br />0= off<br />1= on', '8', '60', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Mask Upcoming Products from being include as New Products', 'SHOW_NEW_PRODUCTS_UPCOMING_MASKED', '0', 'Do you want to mask Upcoming Products from being included as New Products in Listing, Sideboxes and Centerbox?<br />0= off<br />1= on', '21', '30', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Meta Tags - Include Product Model in Title', 'META_TAG_INCLUDE_MODEL', '1', 'Do you want to include the Product Model in the Meta Tag Title?<br /><br />0= off 1= on', '18', '69', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

#
# Updating table get_terms_to_filter
ALTER TABLE get_terms_to_filter ADD get_term_table varchar(64) NOT NULL;
ALTER TABLE get_terms_to_filter ADD get_term_name_field varchar(64) NOT NULL;
UPDATE get_terms_to_filter SET get_term_table = 'TABLE_MANUFACTURERS', get_term_name_field = 'manufacturers_name' where get_term_name = 'manufacturers_id';
UPDATE get_terms_to_filter SET get_term_table = 'TABLE_MUSIC_GENRE', get_term_name_field = 'music_genre_name' where get_term_name = 'music_genre_id';
UPDATE get_terms_to_filter SET get_term_table = 'TABLE_RECORD_COMPANY', get_term_name_field = 'record_company_name' where get_term_name = 'record_company_id';

# Updating coupon-related tables
ALTER TABLE coupons ADD coupon_zone_restriction INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE coupons CHANGE coupon_minimum_order coupon_minimum_order DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000';
ALTER TABLE coupons CHANGE coupon_amount coupon_amount DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000';
ALTER TABLE coupon_gv_customer CHANGE amount amount DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000';
ALTER TABLE coupon_gv_queue CHANGE amount amount DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000';

# Removing auto-increment from these tables
ALTER TABLE meta_tags_categories_description CHANGE COLUMN categories_id  categories_id int(11) NOT NULL;
ALTER TABLE meta_tags_products_description CHANGE COLUMN products_id  products_id int(11) NOT NULL;


################################################################


#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='3.6', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.5->1.3.6', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.6', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.5->1.3.6', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT
