#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.4 to v1.6.0
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2015 Zen Cart Development Team
# * @copyright Portions copyright COWOA authors see https://www.zen-cart.com/downloads.php?do=file&id=1115
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version GIT: $Id: Author: DrByte  New in v1.6.0 $
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
UPDATE configuration set configuration_value = 'true' where configuration_key = 'DOWN_FOR_MAINTENANCE';

# Clear out active customer sessions
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;
TRUNCATE TABLE sessions;

#############
ALTER TABLE configuration ADD val_function text default NULL AFTER set_function;

DELETE FROM configuration WHERE configuration_key = 'SESSION_WRITE_DIRECTORY';

UPDATE configuration set configuration_title = 'Log Page Parse Time', configuration_description = 'Record (to a log file) the time it takes to parse a page' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Destination', configuration_description = 'Directory and filename of the page parse time log' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';
UPDATE configuration set configuration_title = 'Log Date Format', configuration_description = 'The date format' WHERE configuration_key = 'STORE_PARSE_DATE_TIME_FORMAT';
UPDATE configuration set configuration_title = 'Display The Page Parse Time', configuration_description = 'Display the page parse time on the bottom of each page<br />(Note: This DISPLAYS them. You do NOT need to LOG them to merely display them on your site.)' WHERE configuration_key = 'DISPLAY_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Database Queries', configuration_description = 'Record the database queries to files in the system /logs/ folder. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.' WHERE configuration_key = 'STORE_DB_TRANSACTIONS';

UPDATE configuration set configuration_title = 'Enable HTML Emails?', configuration_description = 'Send emails in HTML format if recipient has enabled it in their preferences.' WHERE configuration_key = 'EMAIL_USE_HTML';
UPDATE configuration set configuration_title = 'Email Admin Format?', configuration_description = 'Please select the Admin extra email format (Note: Enable HTML Emails must be on for HTML option to work)' WHERE configuration_key = 'ADMIN_EXTRA_EMAIL_FORMAT';

UPDATE configuration set sort_order = '1', configuration_description = 'Send out e-mails?<br>(Default state is ON.<br>Turn off to suppress ALL outgoing email messages from this store.)' WHERE configuration_key = 'SEND_EMAILS';
UPDATE configuration set sort_order = '2', configuration_description = 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is mostly obsolete and only used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.' WHERE configuration_key = 'EMAIL_TRANSPORT';
UPDATE configuration set configuration_description = 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.<br><br>Default: 25<br>Typical values are:<br>25 - normal unencrypted SMTP<br>587 - encrypted SMTP<br>465 - older MS SMTP port' WHERE configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Search Engines - Disable Indexing', 'ROBOTS_NOINDEX_MAINTENANCE_MODE', 'Normal', 'When in development it is sometimes desirable to discourage search engines from indexing your site. To do that, set this to Maintenance. This will cause a noindex,nofollow tag to be generated on all pages, thus discouraging search engines from indexing your pages until you set this back to Normal.<br>Default: Normal', 1, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'Normal\', \'Maintenance\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Primary Source', 'CURRENCY_SERVER_PRIMARY', 'ecb', 'Where to request external currency updates from (Primary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Secondary Source', 'CURRENCY_SERVER_BACKUP', 'boc', 'Where to request external currency updates from (Secondary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_USER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":4}}}', configuration_description = 'Minimum length of admin usernames (must be 4 or more)' WHERE configuration_key = 'ADMIN_NAME_MINIMUM_LENGTH';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Specials Product Display - Default Sort Order', 'PRODUCT_SPECIALS_LIST_SORT_DEFAULT', '1', 'What Sort Order Default should be used for Specials Display?<br />Default= 1 for Product Name<br /><br />1= Products Name<br />2= Products Name Desc<br />3= Price low to high, Products Name<br />4= Price high to low, Products Name<br />5= Model<br />6= Date Added desc<br />7= Date Added<br />8= Product Sort Order', '19', '8', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'), ', now());
DELETE FROM configuration WHERE configuration_key = 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID';
DELETE FROM configuration WHERE configuration_key = 'ALLOW_GUEST_TO_TELL_A_FRIEND';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Topmost Category Parent ID', 'TOPMOST_CATEGORY_PARENT_ID', '0', 'DEFAULT: 0<br>This is the top-most "parent" category ID. In default installs, this is always 0.  Very advanced customizations might require this to be adjusted. <br>WARNING: Changing this to something other than 0 could result in unpredictable behavior!!', '6', '80', now());

ALTER TABLE configuration DROP PRIMARY KEY, ADD PRIMARY KEY (configuration_key), DROP INDEX unq_config_key_zen, ADD UNIQUE unq_config_id_zen (configuration_id);
ALTER TABLE product_type_layout DROP PRIMARY KEY, ADD PRIMARY KEY (configuration_key), DROP INDEX unq_config_key_zen, ADD UNIQUE unq_config_id_zen (configuration_id);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Image - Click For Larger', 'IMAGE_ENABLE_LARGER_IMAGE_LINKS', '1', 'For Product main-image and additional-images, should a clickable link for popup to see larger image be displayed?<br />0= off<br />1= both<br />2=main image only<br />3=additional images only', 4, 76, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show categories Go To dropdown on Categories/Products', 'SHOW_DISPLAY_CATEGORIES_DROPDOWN_STATUS', 'true', 'Show categories Go To dropdown on Categories/Products?', '1', '19', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Set Download directory chmod setting', 'DOWNLOAD_CHMOD', '755', 'Set Download directory chmod setting, 755 is suggested unless you need another setting on your server.', '13', '3', 'zen_cfg_select_option(array(\'777\', \'755\', \'655\', \'644\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories with Inactive Products Status', 'CATEGORIES_PRODUCTS_INACTIVE_HIDE', '0', 'Hide Categories with Inactive Products?<br />0= off<br />1= on', 19, 30, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

UPDATE configuration set configuration_group_id = 6 where configuration_key in ('PRODUCTS_OPTIONS_TYPE_SELECT', 'UPLOAD_PREFIX', 'TEXT_PREFIX');

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Admin GV Queued Emails To - Status', 'SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_STATUS', '0', 'Send copy of Admin GV Queued Mail Status<br />0= off 1= on', '12', '20', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Admin GV Queued Emails To', 'SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO', '', 'Send copy of Admin GV Queued Mail emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '20', now());

DELETE FROM configuration where configuration_key = 'PHPBB_LINKS_ENABLED' && configuration_value != 'true';


UPDATE countries set address_format_id = 7 where countries_iso_code_3 = 'AUS';
UPDATE countries set address_format_id = 5 where countries_iso_code_3 IN ('BEL', 'NLD', 'SWE', 'ITA');
UPDATE countries set countries_name = 'Åland Islands' where countries_iso_code_3 = 'ALA';
UPDATE countries set countries_name = 'Réunion' where countries_iso_code_3 = 'REU';
UPDATE countries set countries_name = "Côte d'Ivoire" where countries_iso_code_3 = 'CIV';
UPDATE countries set countries_name = 'Bonaire, Sint Eustatius and Saba', countries_iso_code_2 = 'BQ', countries_iso_code_3 = 'BES' WHERE countries_iso_code_3 = 'ANT';
INSERT INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (247,'Curaçao','CW','CUW','1');
INSERT INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (248,'Sint Maarten (Dutch part)','SX','SXM','1');

ALTER TABLE countries ADD INDEX idx_status_zen (status, countries_id);

ALTER TABLE paypal_payment_status_history MODIFY pending_reason varchar(32) default NULL;

ALTER TABLE sessions MODIFY sesskey varchar(255) NOT NULL default '';
ALTER TABLE whos_online MODIFY session_id varchar(255) NOT NULL default '';
ALTER TABLE admin_menus MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY main_page VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_params VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_profiles MODIFY profile_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages_to_profiles MODIFY page_key varchar(255) NOT NULL default '';
UPDATE admin_pages SET sort_order = 1 WHERE page_key = 'users';
UPDATE admin_pages SET sort_order = 2 WHERE page_key = 'profiles';

ALTER TABLE coupons ADD coupon_total TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE coupons ADD coupon_order_limit INT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE coupons_description MODIFY coupon_name VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE orders ADD order_weight FLOAT NOT NULL DEFAULT '0';
ALTER TABLE coupons ADD coupon_is_valid_for_sales TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE orders_products ADD products_weight float NOT NULL default '0';
ALTER TABLE orders_products ADD products_virtual tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD product_is_always_free_shipping tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD products_quantity_order_min float NOT NULL default '1';
ALTER TABLE orders_products ADD products_quantity_order_units float NOT NULL default '1';
ALTER TABLE orders_products ADD products_quantity_order_max float NOT NULL default '0';
ALTER TABLE orders_products ADD products_quantity_mixed tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD products_mixed_discount_quantity tinyint( 1 ) NOT NULL default '1';

ALTER TABLE orders_products_download ADD products_attributes_id int( 11 ) NOT NULL;

ALTER TABLE admin MODIFY COLUMN pwd_last_change_date datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin MODIFY COLUMN last_modified datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin MODIFY COLUMN last_login_date datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin MODIFY COLUMN last_failed_attempt datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin MODIFY admin_pass VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass1 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass2 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass3 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY reset_token VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE customers MODIFY customers_password VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin ADD mobile_phone VARCHAR(20) NOT NULL DEFAULT '' AFTER admin_email;

ALTER TABLE orders MODIFY shipping_method VARCHAR(255) NOT NULL DEFAULT '';

ALTER TABLE coupons ADD coupon_product_count TINYINT(1) NOT NULL DEFAULT '0' AFTER coupon_is_valid_for_sales;

UPDATE query_builder set query_string = 'select max(o.date_purchased) as date_purchased, c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id AND c.customers_newsletter = 1 GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname HAVING max(o.date_purchased) <= subdate(now(),INTERVAL 3 MONTH) ORDER BY c.customers_lastname, c.customers_firstname ASC' where query_name='Dormant Customers (>3months) (Subscribers)';
UPDATE query_builder set query_string = 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o where c.customers_newsletter = \'1\' AND c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC' where query_name='Active customers in past 3 months (Subscribers)';
UPDATE query_builder set query_string = 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC' where query_name='Active customers in past 3 months (Regardless of subscription status)';


DELETE FROM admin_pages WHERE page_key = 'linkpointReview';

UPDATE configuration set configuration_description = 'Show Category Counts in Admin?<br />0=Off<br />1=Always On<br />2=On for subcategories, Off for Top categories' WHERE configuration_key = 'SHOW_COUNTS_ADMIN';
UPDATE configuration set configuration_description = 'Show Category Counts in Admin?<br />0=Off<br />1=Always On<br />2=On for subcategories, Off for Top categories' WHERE configuration_key = 'SHOW_CATEGORY_PRODUCTS_LINKED_STATUS';
UPDATE configuration set set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ' WHERE configuration_key = 'SHOW_COUNTS_ADMIN';
UPDATE configuration set set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ' WHERE configuration_key = 'SHOW_CATEGORY_PRODUCTS_LINKED_STATUS';
UPDATE configuration set configuration_value = '0' WHERE configuration_key = 'SHOW_COUNTS_ADMIN' && configuration_value = 'false';
UPDATE configuration set configuration_value = '1' WHERE configuration_key = 'SHOW_COUNTS_ADMIN' && configuration_value = 'true';
UPDATE configuration set configuration_value = '0' WHERE configuration_key = 'SHOW_CATEGORY_PRODUCTS_LINKED_STATUS' && configuration_value = 'false';
UPDATE configuration set configuration_value = '1' WHERE configuration_key = 'SHOW_CATEGORY_PRODUCTS_LINKED_STATUS' && configuration_value = 'true';

UPDATE configuration SET configuration_title='Credit Card Enable Status - Debit', configuration_key = 'CC_ENABLED_DEBIT', configuration_value ='0', configuration_description='Accept Debit Cards 0= off 1= on<br>NOTE: This is not deeply integrated at this time, and this setting may be redundant if your payment modules do not yet specifically have code to honour this switch.', date_added=now() WHERE configuration_key='CC_ENABLED_SWITCH';

## Guest Checkout
ALTER TABLE customers ADD COLUMN is_guest_account tinyint(1) NOT NULL default 0;
ALTER TABLE orders ADD COLUMN is_guest_order tinyint(1) NOT NULL default 0;
INSERT INTO configuration_group VALUES (NULL, 'Guest Checkout', 'Set Checkout Without an Account', '100', '1');

#NEXT_X_ROWS_AS_ONE_COMMAND:4
SET @t1=0;
SELECT (@t1:=configuration_group_id) as t1 FROM configuration_group WHERE configuration_group_title = 'Guest Checkout';
INSERT INTO admin_pages VALUES ('configGuest','BOX_CONFIGURATION_GUEST','FILENAME_CONFIGURATION',CONCAT('gID=',@t1), 'configuration', 'Y', 31);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
  ('Guest Checkout allowed?', 'GUEST_CHECKOUT_ALLOWED', 'false', 'Enable Guest Checkout? <br />Set to True to allow a customer to checkout without an account.', 26, 10, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
  ('Enable Order Status for Guest Orders', 'GUEST_ORDER_STATUS', 'true', 'Alloq Guests to see Order Status?<br />Set to True so that a Customer that uses Guest Checkout will receive an E-Mail with instructions on how to view the status of their order.', 26, 11, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
  ('Enable Guest E-Mail Only checkout when free', 'GUEST_ALLOW_EMAIL_ONLY', 'false', 'Enable The E-Mail Order Function for Guests?<br />Set to True so that a Guest Customer will only need to enter their E-Mail Address upon checkout if their Cart Balance is 0 (Free).', 26, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
  ('Enable Create Account on Success', 'GUEST_ALLOW_CREATE_ACCOUNT', 'true', 'Allow Guest to create a full account on Checkout Success.', 26, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');


INSERT INTO configuration_group VALUES (NULL, 'Widget Settings', 'Set Widget Configuration Values', '31', '1');
#NEXT_X_ROWS_AS_ONE_COMMAND:4
SET @t1=0;
SELECT (@t1:=configuration_group_id) as t1 FROM configuration_group WHERE configuration_group_title = 'Widget Settings';
INSERT INTO admin_pages VALUES ('configWidgets','BOX_CONFIGURATION_WIDGET','FILENAME_CONFIGURATION',CONCAT('gID=',@t1), 'configuration', 'Y', 32);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
('Max Error Logs', 'MAX_ERROR_LOGS', '20', 'Display this number of error logs', @t1, '1', now(), NULL, NULL);

INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order) VALUES ('system_inspection', 'BOX_TOOLS_SYSTEM_INSPECTION', 'FILENAME_SYSTEM_INSPECTION', '', 'tools', 'Y', 14) ;

INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string ) VALUES ( '', 'email,newsletters', 'Permanent Account Holders Only', 'Send email only to permanent account holders ', 'select customers_email_address, customers_firstname, customers_lastname from TABLE_CUSTOMERS where is_guest_account != 1 order by customers_lastname, customers_firstname, customers_email_address');

# --------------------------------------------------------

DROP TABLE IF EXISTS dashboard_widgets_groups;
CREATE TABLE IF NOT EXISTS dashboard_widgets_groups (
  widget_group varchar(64) NOT NULL,
  language_id int(11) NOT NULL DEFAULT '1',
  widget_group_name varchar(255) NOT NULL,
  PRIMARY KEY (widget_group,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'dashboard_widgets'
#

DROP TABLE IF EXISTS dashboard_widgets;
CREATE TABLE IF NOT EXISTS dashboard_widgets (
  widget_key varchar(64) NOT NULL,
  widget_group varchar(64) NOT NULL,
  widget_status int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (widget_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'dashboard_widgets_description'
#

DROP TABLE IF EXISTS dashboard_widgets_description;
CREATE TABLE IF NOT EXISTS dashboard_widgets_description (
  widget_key varchar(64) NOT NULL,
  widget_name varchar(255) NOT NULL,
  widget_description text NOT NULL,
  language_id int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (widget_key,language_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'dashboard_widgets_to_profiles'
#

DROP TABLE IF EXISTS dashboard_widgets_to_profiles;
CREATE TABLE IF NOT EXISTS dashboard_widgets_to_profiles (
  profile_id int(11) NOT NULL,
  widget_key varchar(64) NOT NULL,
  PRIMARY KEY (profile_id,widget_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'dashboard_widgets_to_users'
#

DROP TABLE IF EXISTS dashboard_widgets_to_users;
CREATE TABLE IF NOT EXISTS dashboard_widgets_to_users (
  widget_key varchar(64) NOT NULL,
  admin_id int(11) NOT NULL,
  widget_row int(11) NOT NULL DEFAULT '0',
  widget_column int(11) NOT NULL DEFAULT '0',
  widget_refresh int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (widget_key,admin_id)
) ENGINE=MyISAM;

#
# Set up default widgets
#
INSERT INTO dashboard_widgets (widget_key, widget_group, widget_status) VALUES
('general-statistics', 'general-statistics', 1),
('order-summary', 'order-statistics', 1),
('new-customers', 'new-customers', 1),
('counter-history', 'counter-history', 1),
('new-orders', 'new-orders', 1),
('logs', 'logs', 1)
;

INSERT INTO dashboard_widgets_description (widget_key, widget_name, widget_description, language_id) VALUES
('general-statistics', 'GENERAL_STATISTICS', '', 1),
('order-summary', 'ORDER_SUMMARY', '', 1),
('new-customers', 'NEW_CUSTOMERS', '', 1),
('counter-history', 'COUNTER_HISTORY', '', 1),
('new-orders', 'NEW_ORDERS', '', 1),
('logs', 'LOGS', '', 1)
;

INSERT INTO dashboard_widgets_groups (widget_group, language_id, widget_group_name) VALUES
('general-statistics', 1, 'GENERAL_STATISTICS_GROUP'),
('order-statistics', 1, 'ORDER_STATISTICS_GROUP'),
('new-customers', 1, 'NEW_CUSTOMERS_GROUP'),
('counter-history', 1, 'COUNTER_HISTORY_GROUP'),
('new-orders', 1, 'NEW_ORDERS_GROUP'),
('logs', 1, 'LOGS_GROUP')
;

# default widgets for first user
INSERT INTO dashboard_widgets_to_users (widget_key, admin_id, widget_row, widget_column) VALUES
('general-statistics', 1, 0, 0),
('order-summary', 1, 1, 0),
('new-customers', 1, 0, 1),
('counter-history', 1, 1, 1),
('new-orders', 1, 0, 2),
('logs', 1, 1, 2);


INSERT INTO dashboard_widgets (widget_key, widget_group, widget_status) VALUES ('banner-statistics', 'banner-statistics', 1);
INSERT INTO dashboard_widgets_description (widget_key, widget_name, widget_description, language_id) VALUES ('banner-statistics', 'Banner Statistics', '', 1);
INSERT INTO dashboard_widgets_groups (widget_group, language_id, widget_group_name) VALUES ('banner-statistics', 1, 'Banner Statistics');


# --------------------------------------------------------

#
# Table structure for table 'listingbox_locations'
#

DROP TABLE IF EXISTS listingbox_locations;
CREATE TABLE listingbox_locations (
  location_key varchar(40) NOT NULL,
  location_name varchar(255) NOT NULL,
  PRIMARY KEY (location_key)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS listingboxgroups;
CREATE TABLE listingboxgroups (
  group_id int(11) NOT NULL AUTO_INCREMENT,
  group_name varchar(255) NOT NULL,
  PRIMARY KEY (group_id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS listingboxgroups_to_locations;
CREATE TABLE listingboxgroups_to_locations (
  group_id int(11) NOT NULL,
  location_key varchar(40) NOT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY main1 (group_id, location_key)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS listingboxes_to_listingboxgroups;
CREATE TABLE IF NOT EXISTS listingboxes_to_listingboxgroups (
  listingbox varchar(80) NOT NULL,
  group_id int(11) NOT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY main1 (listingbox, group_id)
) ENGINE=MyISAM;

INSERT INTO listingbox_locations (location_key, location_name) VALUES
('INDEX_DEFAULT', 'Index Page - Default'),
('MISSING_PRODUCT', 'Missing Product'),
('EMPTY_CART', 'Shopping Cart - Empty');

INSERT INTO listingboxgroups (group_id, group_name) VALUES
(1, 'Featured - Specials - New - Upcoming');

INSERT INTO listingboxgroups_to_locations (group_id, location_key, sort_order) VALUES
(1, 'INDEX_DEFAULT', 0),
(1, 'MISSING_PRODUCT', 0),
(1, 'EMPTY_CART', 0);

INSERT INTO listingboxes_to_listingboxgroups (listingbox, group_id, sort_order) VALUES
('FeaturedProductsCenter', 1, 0),
('NewProductsCenter', 1, 3),
('SpecialsProductsCenter', 1, 2),
('UpcomingProductsCenter', 1, 4);

## CHANGE-346 - Fix outdated language in configuration menu help texts
## CHANGE-411 increase size of fileds in admin profile related tables
## CHANGE-367 - Dashboard Widgets for 1.6 Admin Home Page (including ajax infrastructure)

#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='6.0-pre-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.6.0-pre-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='6.0-pre-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.6.0-pre-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT

