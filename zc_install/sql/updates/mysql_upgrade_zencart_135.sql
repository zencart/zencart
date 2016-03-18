#
# * This SQL script upgrades the core Zen Cart database structure from v1.3.0.2 to v1.3.5
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2016 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
#

## v1301 steps included here:
#insert for those who did an upgrade from 1.2.x to 1.3.0 and thus didn't get the insert:
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Site Map Status', 'DEFINE_SITE_MAP_STATUS', '1', 'Enable the Defined Site Map Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '67', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
REPLACE INTO configuration_group VALUES ('30', 'EZ-Pages Settings', 'EZ-Pages Settings', 30, '1');
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ' WHERE configuration_key = 'SHOW_TOTALS_IN_CART';
# move to right group (Layout, not prod-info)
update configuration set configuration_group_id=19 where configuration_key = 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP';
#rename descriptions to be more logical
update configuration set configuration_title = 'Send Notice of Pending Reviews Emails To - Status' where configuration_key='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS';
update configuration set configuration_title = 'Send Notice of Pending Reviews Emails To' where configuration_key='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO';
UPDATE configuration set configuration_title='Send Low Stock Emails' where configuration_key = 'SEND_LOWSTOCK_EMAIL';
UPDATE configuration set configuration_title='Send Low Stock Emails To' where configuration_key = 'SEND_EXTRA_LOW_STOCK_EMAILS_TO';
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.1', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0->1.3.0.1', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.1', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0->1.3.0.1', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';
# eof 1301
# v1302 steps included here:
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Upload prefix', 'UPLOAD_PREFIX', 'upload_', 'Prefix used to differentiate between upload options and other options', 0, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Text prefix', 'TEXT_PREFIX', 'txt_', 'Prefix used to differentiate between text option values and other option values', 0, NULL, now(), now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Meta Tags Generated Description Maximum Length?', 'MAX_META_TAG_DESCRIPTION_LENGTH', '50', 'Set Generated Meta Tag Description Maximum Length to (words) Default 50:', '18', '71', '', '', now());
#tidy sort order for consistency between upgrades and fresh installs
UPDATE configuration SET sort_order=115 WHERE configuration_key = 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP';
#table alterations fixed in 1.3.5 but best completed here also for upgraders:
ALTER TABLE query_builder CHANGE COLUMN query_category query_category varchar(40) NOT NULL default '';
ALTER TABLE query_builder CHANGE COLUMN query_name query_name varchar(80) NOT NULL default '';
ALTER TABLE query_builder CHANGE COLUMN query_description query_description TEXT NOT NULL;
ALTER TABLE query_builder CHANGE COLUMN query_string query_string TEXT NOT NULL;
ALTER TABLE query_builder CHANGE COLUMN query_keys_list query_keys_list TEXT NOT NULL;
REPLACE INTO query_builder ( query_category , query_name , query_description , query_string, query_keys_list) VALUES ('email,newsletters', 'Administrator', 'Just the email account of the current administrator', 'select \'ADMIN\' as customers_firstname, admin_name as customers_lastname, admin_email as customers_email_address from TABLE_ADMIN where admin_id = $SESSION:admin_id', '');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('State - Always display as pulldown?', 'ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN', 'false', 'When state field is displayed, should it always be a pulldown menu?', 5, '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0.1->1.3.0.2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0.1->1.3.0.2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';
#eof 1302



## CONFIGURATION TABLE
UPDATE configuration SET configuration_description = 'Set the Default Customer Default Email Preference<br />0= Text<br />1= HTML<br />' WHERE configuration_key = 'ACCOUNT_EMAIL_PREFERENCE';
UPDATE configuration SET configuration_description = 'Show on Shopping Cart Update Cart Button Location as:<br /><br />1= Next to each Qty Box<br />2= Below all Products<br />3= Both Next to each Qty Box and Below all Products' where configuration_key = 'SHOW_SHOPPING_CART_UPDATE';
UPDATE configuration SET configuration_title = 'Display Product Add to Cart Button (0=off; 1=on; 2=on with Qty Box per Product)', configuration_description = 'Do you want to display the Add to Cart Button?<br /><br /><strong>NOTE:</strong> Turn OFF Display Multiple Products Qty Box Status to use Option 2 on with Qty Box per Product' WHERE configuration_key= 'PRODUCT_LIST_PRICE_BUY_NOW';
UPDATE configuration SET configuration_description = 'Customer should be asked about product notifications after checkout success and in account preferences<br />0= Never ask<br />1= Ask (ignored on checkout if has already selected global notifications)<br /><br />Note: Sidebox must be turned off separately' WHERE configuration_key = 'CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS';
UPDATE configuration set configuration_value='false' where configuration_key ='STORE_DB_TRANSACTIONS';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Alpha Sorter Dropdown', 'PRODUCT_LIST_ALPHA_SORTER', 'false', 'Do you want to include an Alpha Filter dropdown on the Product Listing?', '8', '50', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Sub Categories Image', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS', 'false', 'Do you want to include the Sub Categories Image on the Product Listing?', '8', '52', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Include Product Listing Top Categories Image', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS_TOP', 'false', 'Do you want to include the Top Categories Image on the Product Listing?', '8', '53', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

# need to change
# 'Previous Next - Navigation Includes Category Position', 'PRODUCT_INFO_CATEGORIES
DELETE from configuration where configuration_key = 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP' and configuration_group_id = 18;

UPDATE product_type_layout SET configuration_description = 'Display Record Company on Product Info 0= off 1= on' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_RECORD_COMPANY';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('cURL Proxy Status', 'CURL_PROXY_REQUIRED', 'False', 'Does your host require that you use a proxy for cURL communication?', '1', '50', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('cURL Proxy Address', 'CURL_PROXY_SERVER_DETAILS', '', 'If you have GoDaddy hosting or other hosting services that require use of a proxy to talk to external sites via cURL, enter their proxy address here.<br />format: address:port<br />ie: for GoDaddy, enter: 64.202.165.130:3128', 1, 51, NULL, now(), NULL, NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Previous Next - Navigation Includes Category Name and Image Status', 'PRODUCT_INFO_CATEGORIES_IMAGE_STATUS', '2', 'Product\'s Category Image and Name Status<br />0= Category Name and Image always shows<br />1= Category Name only<br />2= Category Name and Image when not blank', 18, 20, now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Category Name and Image Always\'), array(\'id\'=>\'1\', \'text\'=>\'Category Name only\'), array(\'id\'=>\'2\', \'text\'=>\'Category Name and Image when not blank\')),');


#Other
UPDATE banners SET banners_url='http://www.zen-cart.com/partners/payment' where banners_url='http://www.zen-cart.com/modules/freecontent/index.php?id=29';
UPDATE query_builder SET query_string = 'select o.date_purchased, c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id AND c.customers_newsletter = 1 GROUP BY c.customers_email_address HAVING max(o.date_purchased) <= subdate(now(),INTERVAL 3 MONTH) ORDER BY c.customers_lastname, c.customers_firstname ASC' WHERE query_name = 'Dormant Customers (>3months) (Subscribers)';


################################################################

#table alterations
ALTER TABLE query_builder CHANGE COLUMN query_category query_category varchar(40) NOT NULL default '';
ALTER TABLE query_builder CHANGE COLUMN query_name query_name varchar(80) NOT NULL default '';
ALTER TABLE query_builder CHANGE COLUMN query_description query_description TEXT NOT NULL;
ALTER TABLE query_builder CHANGE COLUMN query_string query_string TEXT NOT NULL;
ALTER TABLE query_builder CHANGE COLUMN query_keys_list query_keys_list TEXT NOT NULL;


#Index Optimizations
ALTER TABLE layout_boxes ADD INDEX idx_layout_box_sort_order_zen (layout_box_sort_order);
ALTER TABLE media_clips ADD INDEX idx_clip_type_zen (clip_type);
ALTER TABLE media_manager ADD INDEX idx_media_name_zen (media_name);
ALTER TABLE media_types ADD INDEX idx_type_name_zen (type_name);
ALTER TABLE products ADD INDEX idx_products_date_available_zen (products_date_available);
ALTER TABLE products ADD INDEX idx_products_ordered_zen (products_ordered);
ALTER TABLE products ADD INDEX idx_products_model_zen (products_model);
ALTER TABLE products ADD INDEX idx_products_price_sorter_zen (products_price_sorter);
ALTER TABLE products ADD INDEX idx_master_categories_id_zen (master_categories_id);
ALTER TABLE products ADD INDEX idx_products_sort_order_zen (products_sort_order);
ALTER TABLE products ADD INDEX idx_manufacturers_id_zen (manufacturers_id);
ALTER TABLE products_attributes ADD INDEX idx_opt_sort_order_zen (products_options_sort_order);
ALTER TABLE products_options ADD INDEX idx_products_options_sort_order_zen (products_options_sort_order);
ALTER TABLE products_options ADD INDEX idx_products_options_name_zen (products_options_name);
ALTER TABLE products_options_values ADD INDEX idx_products_options_values_name_zen (products_options_values_name);
ALTER TABLE products_options_values ADD INDEX idx_products_options_values_sort_order_zen (products_options_values_sort_order);
ALTER TABLE products_options_values_to_products_options ADD INDEX idx_products_options_id_zen (products_options_id);
ALTER TABLE products_options_values_to_products_options ADD INDEX idx_products_options_values_id_zen (products_options_values_id);
ALTER TABLE product_music_extra ADD INDEX idx_artists_id_zen (artists_id);
ALTER TABLE product_music_extra ADD INDEX idx_record_company_id_zen (record_company_id);

ALTER TABLE admin ADD INDEX idx_admin_email_zen (admin_email);
ALTER TABLE banners ADD INDEX idx_expires_date_zen (expires_date);
ALTER TABLE banners ADD INDEX idx_date_scheduled_zen (date_scheduled);
ALTER TABLE featured ADD INDEX idx_expires_date_zen (expires_date);
ALTER TABLE specials ADD INDEX idx_expires_date_zen (expires_date);
ALTER TABLE orders ADD INDEX idx_date_purchased_zen (date_purchased);

ALTER TABLE countries ADD INDEX idx_address_format_id_zen (address_format_id);
ALTER TABLE countries ADD INDEX idx_iso_2_zen (countries_iso_code_2);
ALTER TABLE countries ADD INDEX idx_iso_3_zen (countries_iso_code_3);
ALTER TABLE zones ADD INDEX idx_zone_country_id_zen (zone_country_id);
ALTER TABLE zones ADD INDEX idx_zone_code_zen (zone_code);
ALTER TABLE zones_to_geo_zones ADD INDEX idx_zones_zen (geo_zone_id, zone_country_id, zone_id);

ALTER TABLE product_type_layout ADD INDEX idx_type_id_sort_order_zen (product_type_id, sort_order) ;

ALTER TABLE reviews ADD INDEX idx_status_zen (status);
ALTER TABLE reviews ADD INDEX idx_date_added_zen (date_added);

ALTER TABLE salemaker_sales ADD INDEX idx_sale_date_start_zen (sale_date_start);
ALTER TABLE salemaker_sales ADD INDEX idx_sale_date_end_zen (sale_date_end);

#
ALTER TABLE authorizenet ADD PRIMARY KEY (id );
ALTER TABLE authorizenet DROP INDEX idx_auth_net_id;


#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='3.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0.2->1.3.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0.2->1.3.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT

