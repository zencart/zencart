#
# * This SQL script upgrades the core Zen Cart database structure from v1.3.7 to v1.3.8
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2007 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: mysql_upgrade_zencart_137_to_138.sql 15140 2009-12-31 04:17:24Z drbyte $
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
# * d. On the Database Upgrade screen, you'll be presented with a list of checkboxes for
# *    various Zen Cart versions, with the recommended upgrades already pre-selected.
# * e. Verify the checkboxes, then scroll down and enter your Zen Cart Admin username
# *    and password, and then click on the Upgrade button.
# * f. If any errors occur, you will be notified.  Some warnings can be ignored.
# * g. When done, you'll be taken to the Finished page.
#
#####################################################

#Change Canada's code for Newfoundland from NF to NL, according to 2002 ISO standards change.
UPDATE zones SET zone_code = 'NL' where zone_country_id = 38 and zone_name = 'Newfoundland';


## CONFIGURATION TABLE
UPDATE configuration set configuration_description = 'Categories/Products Display Sort Order<br />0= Categories/Products Sort Order/Name<br />1= Categories/Products Name<br />2= Products Model<br />3= Products Qty+, Products Name<br />4= Products Qty-, Products Name<br />5= Products Price+, Products Name<br />6= Products Price-, Products Name' WHERE configuration_key = 'CATEGORIES_PRODUCTS_SORT_ORDER';
UPDATE configuration set set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'), ' where configuration_key = 'SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS';
UPDATE configuration set configuration_description = 'Number of new products listed per page' WHERE configuration_key='MAX_DISPLAY_PRODUCTS_NEW';

UPDATE configuration set configuration_description = 'If you have GoDaddy hosting or other hosting services that require use of a proxy to talk to external sites via cURL, enter their proxy address here.<br />format: address:port<br />ie: for GoDaddy, enter: <strong>proxy.shr.secureserver.net:3128</strong> or possibly 64.202.165.130:3128' WHERE configuration_key='CURL_PROXY_SERVER_DETAILS';
UPDATE configuration set configuration_description = 'Does your mailserver require that all outgoing emails have their "from" address match a known domain that exists on your webserver?<br /><br />This is often required in order to prevent spoofing and spam broadcasts.  If set to Yes, this will cause the email address (sent FROM) to be used as the "from" address on all outgoing mail.' where configuration_key = 'EMAIL_SEND_MUST_BE_STORE';
UPDATE configuration set use_function = 'zen_cfg_password_display' where configuration_key = 'EMAIL_SMTPAUTH_PASSWORD';

UPDATE configuration set configuration_value = 150, configuration_description = 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', set_function = '' WHERE configuration_key = 'PRODUCT_NEW_LIST_DESCRIPTION';
UPDATE configuration set configuration_value = 150, configuration_description = 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', set_function = '' WHERE configuration_key = 'PRODUCT_FEATURED_LIST_DESCRIPTION';
UPDATE configuration set configuration_value = 150, configuration_description = 'How many characters do you want to display of the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', set_function = '' WHERE configuration_key = 'PRODUCT_ALL_LIST_DESCRIPTION';

#UPDATE TO NEW RANDOMIZATION ON SIDEBOXES TO ALLOW MORE THAN ONE
UPDATE configuration set configuration_value = '1', configuration_title = 'Random Product Reviews for SideBox', configuration_description = 'This is the number of random product REVIEWS to rotate in the sidebox<br /><br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?' WHERE configuration_key='MAX_RANDOM_SELECT_REVIEWS';
UPDATE configuration set configuration_value = '1', configuration_title = 'Random New Products for SideBox', configuration_description = 'This is the number of random NEW products to rotate in the sidebox<br /><br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?' WHERE configuration_key='MAX_RANDOM_SELECT_NEW';
UPDATE configuration set configuration_value = '1', configuration_title = 'Random Products On Special for SideBox', configuration_description = 'This is the number of random products on SPECIAL to rotate in the sidebox<br /><br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?' WHERE configuration_key='MAX_RANDOM_SELECT_SPECIALS';
UPDATE configuration set configuration_value = '1', configuration_title = 'Random Featured Products for SideBox', configuration_description = 'This is the number of random FEATURED products to rotate in the sidebox<br /><br />Enter the number of products to display in this sidebox at one time.<br /><br />How many products do you want to display in this sidebox?' WHERE configuration_key='MAX_RANDOM_SELECT_FEATURED_PRODUCTS';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Notice of Combining Shopping Cart on Login', 'SHOW_SHOPPING_CART_COMBINED', '1', 'When a customer logs in and has a previously stored shopping cart, the products are combined with the existing shopping cart.<br /><br />Do you wish to display a Notice to the customer?<br /><br />0= OFF, do not display a notice<br />1= Yes show notice and go to shopping cart<br />2= Yes show notice, but do not go to shopping cart', '9', '35', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Display Order Comments on Admin Invoice', 'ORDER_COMMENTS_INVOICE', '1', 'Do you want to display the Order Comments on the Admin Invoice?<br />0= OFF<br />1= First Comment by Customer only<br />2= All Comments for the Order', 7, 25, now(), NULL, 'zen_cfg_select_option(array(''0'', ''1'', ''2''), ');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Display Order Comments on Admin Packing Slip', 'ORDER_COMMENTS_PACKING_SLIP', '1', 'Do you want to display the Order Comments on the Admin Packing Slip?<br />0= OFF<br />1= First Comment by Customer only<br />2= All Comments for the Order', 7, 26, now(), NULL, 'zen_cfg_select_option(array(''0'', ''1'', ''2''), ');

#audience list addition: customers who have signed up but never made a purchase
INSERT INTO query_builder (query_category, query_name, query_description , query_string) VALUES ('email,newsletters', 'Customers who have never completed a purchase', 'For sending newsletter to all customers who registered but have never completed a purchase', 'SELECT DISTINCT c.customers_email_address as customers_email_address, c.customers_lastname as customers_lastname, c.customers_firstname as customers_firstname FROM TABLE_CUSTOMERS c LEFT JOIN  TABLE_ORDERS o ON c.customers_id=o.customers_id WHERE o.date_purchased IS NULL');

#admin
ALTER TABLE admin_activity_log CHANGE page_parameters page_parameters text;


#Allow for viewing
ALTER TABLE orders_products_attributes CHANGE products_options_values products_options_values text NOT NULL;

## PP EC table structure fixes ... needed longer fields in several cases for storing archive data and keep structures in sync
ALTER TABLE paypal CHANGE txn_type txn_type varchar(40) NOT NULL default '';
ALTER TABLE paypal CHANGE reason_code reason_code varchar(40) default NULL;
ALTER TABLE paypal CHANGE payment_type payment_type varchar(40) NOT NULL default '';
ALTER TABLE paypal CHANGE payment_status payment_status varchar(32) NOT NULL default '';
ALTER TABLE paypal CHANGE pending_reason pending_reason varchar(32) default NULL;
ALTER TABLE paypal CHANGE invoice invoice varchar(128) default NULL;
ALTER TABLE paypal CHANGE payer_business_name payer_business_name varchar(128) default NULL;
ALTER TABLE paypal CHANGE address_name address_name varchar(64) default NULL;
ALTER TABLE paypal CHANGE address_street address_street varchar(254) default NULL;
ALTER TABLE paypal CHANGE address_city address_city varchar(120) default NULL;
ALTER TABLE paypal CHANGE address_state address_state varchar(120) default NULL;
ALTER TABLE paypal CHANGE payer_email payer_email varchar(128) NOT NULL default '';
ALTER TABLE paypal CHANGE business business varchar(128) NOT NULL default '';
ALTER TABLE paypal CHANGE receiver_email receiver_email varchar(128) NOT NULL default '';
ALTER TABLE paypal CHANGE txn_id txn_id varchar(20) NOT NULL default '';
ALTER TABLE paypal CHANGE parent_txn_id parent_txn_id varchar(20) default NULL;
ALTER TABLE paypal ADD COLUMN module_name varchar(40) NOT NULL default '' after txn_type;
ALTER TABLE paypal ADD COLUMN module_mode varchar(40) NOT NULL default '' after module_name;

ALTER TABLE paypal_testing CHANGE txn_type txn_type varchar(40) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE reason_code reason_code varchar(40) default NULL;
ALTER TABLE paypal_testing CHANGE payment_type payment_type varchar(40) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE payment_status payment_status varchar(32) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE pending_reason pending_reason varchar(32) default NULL;
ALTER TABLE paypal_testing CHANGE invoice invoice varchar(128) default NULL;
ALTER TABLE paypal_testing CHANGE payer_business_name payer_business_name varchar(128) default NULL;
ALTER TABLE paypal_testing CHANGE address_name address_name varchar(64) default NULL;
ALTER TABLE paypal_testing CHANGE address_street address_street varchar(254) default NULL;
ALTER TABLE paypal_testing CHANGE address_city address_city varchar(120) default NULL;
ALTER TABLE paypal_testing CHANGE address_state address_state varchar(120) default NULL;
ALTER TABLE paypal_testing CHANGE payer_email payer_email varchar(128) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE business business varchar(128) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE receiver_email receiver_email varchar(128) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE txn_id txn_id varchar(20) NOT NULL default '';
ALTER TABLE paypal_testing CHANGE parent_txn_id parent_txn_id varchar(20) default NULL;
ALTER TABLE paypal_testing ADD COLUMN module_name varchar(40) NOT NULL default '' after txn_type;
ALTER TABLE paypal_testing ADD COLUMN module_mode varchar(40) NOT NULL default '' after module_name;

ALTER TABLE paypal_session CHANGE saved_session saved_session mediumblob NOT NULL;

# Change TEXT field to varchar(50) since TEXT was a waste of space
ALTER TABLE authorizenet CHANGE authorization_type authorization_type varchar(50) NOT NULL default '';

# change cache tracking size
TRUNCATE TABLE db_cache;
ALTER TABLE db_cache CHANGE cache_data cache_data mediumblob;
TRUNCATE TABLE sessions;
ALTER TABLE sessions CHANGE value value mediumblob NOT NULL;

#support longer ezpages content
ALTER TABLE ezpages CHANGE pages_html_text pages_html_text mediumtext;

#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='3.8', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.7->1.3.8', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.8', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.7->1.3.8', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';



### LEFT FOR END IN CASE ERRORS ARE ENCOUNTERED:
#rename zen_order_id field to order_id to minimize confusion for folks who decide to manually edit their databases
ALTER TABLE paypal CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0';
ALTER TABLE paypal_testing CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0';
ALTER TABLE linkpoint_api CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0';



#####  END OF UPGRADE SCRIPT
