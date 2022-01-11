#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.7 to v1.5.8
# *
# * @access private
# * @copyright Copyright 2003-2021 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id:  New in v1.5.8 $
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
# * f. If any errors occur, you will be notified. Some warnings can be ignored.
# * g. When done, you will be taken to the Finished page.
#
#####################################################

# Clear out active customer sessions. Truncating helps the database clean up behind itself.
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;

ALTER TABLE layout_boxes ADD plugin_details varchar(100) NOT NULL default '';
ALTER TABLE manufacturers ADD COLUMN featured tinyint default 0;
ALTER TABLE customers ADD registration_ip varchar(45) NOT NULL default '';
ALTER TABLE customers ADD last_login_ip varchar(45) NOT NULL default '';
ALTER TABLE customers_info ADD INDEX idx_date_created_cust_id_zen (customers_info_date_account_created, customers_info_id);

ALTER TABLE orders_products MODIFY products_name varchar(191) NOT NULL default '';
ALTER TABLE products_description MODIFY products_name varchar(191) NOT NULL default '';

ALTER TABLE orders MODIFY customers_country varchar(64) NOT NULL default ''; 
ALTER TABLE orders MODIFY delivery_country varchar(64) NOT NULL default ''; 
ALTER TABLE orders MODIFY billing_country varchar(64) NOT NULL default ''; 

# Remove greater-than sign in query_builder
UPDATE query_builder SET query_name = 'Customers Dormant for 3+ months (Subscribers)' WHERE query_id = 3;

# Remove deprecated defines
DELETE FROM configuration WHERE configuration_key = 'CATEGORIES_SPLIT_DISPLAY';
DELETE FROM configuration WHERE configuration_key = 'CUSTOMERS_AUTHORIZATION_PRICES_OFF';
DELETE FROM configuration WHERE configuration_key = 'EMAIL_FRIENDLY_ERRORS';
DELETE FROM configuration WHERE configuration_key = 'EMAIL_LINEFEED';
DELETE FROM configuration WHERE configuration_key = 'CC_CVV_MIN_LENGTH';
DELETE FROM configuration WHERE configuration_key = 'MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER';


# Update configuration descriptions
UPDATE configuration SET configuration_description = 'Enter your PayPal Merchant ID here. This is used for the more user-friendly In-Context checkout mode. You can obtain this value by going to your PayPal account, clicking on your account name at the top right, then clicking Account Settings, and navigating to the Business Information section; You will find your Merchant Account ID on that screen. A typical Merchant ID looks like FDEFDEFDEFDE11.' WHERE configuration_key = 'MODULE_PAYMENT_PAYPALWPP_MERCHANTID';
UPDATE configuration SET configuration_description = 'If there is no weight to the order, does the order have Free Shipping?<br>0= no<br>1= yes<br><br>Note: When using Free Shipping, Enable the Free Shipping Module.  It will only show when shipping is free.' WHERE configuration_key = 'ORDER_WEIGHT_ZERO_STATUS';
UPDATE configuration SET configuration_title = 'Category Header Menu ON/OFF', configuration_description = 'Category Header Nav<br />This enables the display of your store\'s categories as a menu across the top of your header. There are many potential creative uses for this.<br />0= Hide Categories Tabs<br />1= Show Categories Tabs' WHERE configuration_key = 'CATEGORIES_TABS_STATUS';

UPDATE configuration SET configuration_description = 'Defines the method for sending mail.<br><br><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br><strong>smtpauth</strong> should be used by most sites, as it provides secure sending of authenticated email. You must also configure your smtpauth settings in the appropriate fields in this admin section.<br><strong>Gmail</strong> is used for sending emails using the Google mail service, and requires the [less secure] setting enabled in your gmail account.<br><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br><strong>sendmail-f</strong> is only for servers which require the use of the -f parameter to use sendmail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br><strong>Qmail</strong> is used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.<br><br>MOST SITES WILL USE [smtpauth].', set_function='zen_cfg_select_option(array(\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Gmail\',\'Qmail\'),' WHERE configuration_key = 'EMAIL_TRANSPORT';


UPDATE configuration SET configuration_description = 'Customers Referral Code is created from<br />0= Off<br />1= 1st Discount Coupon Code used<br />2= Customer can add during create account or edit if blank<br /><br />NOTE: Once the Customers Referral Code has been set it can only be changed by the Administrator' WHERE configuration_key = 'CUSTOMERS_REFERRAL_STATUS';

UPDATE configuration SET configuration_description = 'The shipping cost may be calculated based on the total weight of the items ordered, the total price of the items ordered, or the total number of items ordered.' WHERE configuration_key = 'MODULE_SHIPPING_TABLE_MODE';

UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing an index listing' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_LISTING';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing All Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_ALL';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing New Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_NEW';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing Featured Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS';

UPDATE configuration SET configuration_title = 'New Products Centerbox', configuration_description = 'Number of products to display in the New Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_NEW_PRODUCTS';
UPDATE configuration SET configuration_title = 'Products on Special Centerbox', configuration_description = 'Number of products to display in the Products on Special centerbox' WHERE configuration_key = 'MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX';
UPDATE configuration SET configuration_title = 'Upcoming Products Centerbox', configuration_description = 'Number of products to display in the Upcoming Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_UPCOMING_PRODUCTS';
UPDATE configuration SET configuration_title = 'Featured Products Centerbox', configuration_description = 'Number of products to display in the Featured Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED';

UPDATE configuration SET configuration_title = 'Products on Special Page', configuration_description = 'Number of products to display per page on the Specials page' WHERE configuration_key = 'MAX_DISPLAY_SPECIAL_PRODUCTS';
UPDATE configuration SET configuration_title = 'All Products Page', configuration_description = 'Number of products to display per page on the All Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_ALL';
UPDATE configuration SET configuration_title = 'Featured Products Page', configuration_description = 'Number of products to display per page on the Featured Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS';
UPDATE configuration SET configuration_title = 'New Products Page', configuration_description = 'Number of products to display per page on the New Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_NEW';
UPDATE configuration SET configuration_title = 'Products Listing Page', configuration_description = 'Number of products to display per page on a Listing page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_LISTING';

#############
# Incorporate setting for Column-Grid-Layout template control
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Columns Per Row', 'PRODUCT_LISTING_COLUMNS_PER_ROW', '1', 'Select the number of columns of products to show per row in the product listing.<br>Recommended: 3<br>1=[rows] mode.', '8', '45', NULL, now(), NULL, NULL);


#############

INSERT IGNORE INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('customerGroups', 'BOX_CUSTOMERS_CUSTOMER_GROUPS', 'FILENAME_CUSTOMER_GROUPS', '', 'customers', 'Y', 3);

CREATE TABLE customer_groups (
  group_id int UNSIGNED NOT NULL AUTO_INCREMENT,
  group_name varchar(191) NOT NULL,
  group_comment varchar(255),
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (group_id),
  UNIQUE KEY idx_groupname_zen (group_name)
);
CREATE TABLE customers_to_groups (
  id int UNSIGNED NOT NULL AUTO_INCREMENT,
  group_id int UNSIGNED NOT NULL,
  customer_id int UNSIGNED NOT NULL,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_custid_groupid_zen (customer_id, group_id),
  KEY idx_groupid_custid_zen (group_id, customer_id)
);

#############

### Added in v157a, including here in case upgrades missed it
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Columns Per Row', 'PRODUCT_LISTING_COLUMNS_PER_ROW', '1', 'Select the number of columns of products to show per row in the product listing.<br>Recommended: 3<br>1=[rows] mode.', '8', '45', NULL, now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Disabled Product Status for Search Engines', 'DISABLED_PRODUCTS_TRIGGER_HTTP200', 'false', 'When a product is marked Disabled (status=0) but is not deleted from the database, should Search Engines still show it as Available?<br>eg:<br>True = Return HTTP 200 response<br>False = Return HTTP 410<br>(Deleting it will return HTTP 404)<br><b>Default: false</b>', '9', '10', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
#############


#############
### Added v158 bring address formats up to date
### Updated address formats
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city$cr$country' , address_summary = '$city'  WHERE address_format_id = 1;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city $state    $postcode$cr$country' , address_summary = '$city $state $postcode' WHERE address_format_id = 2;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$postcode $city $state$cr$country' , address_summary = '$postcode $city $state' WHERE address_format_id = 3;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city $postcode$cr$country' , address_summary = '$city $postcode' WHERE address_format_id = 4;
UPDATE address_format SET address_format = '$firstname $lastnameupper$cr$streets$cr$postcode  $city$cr$country' , address_summary = '$postcode $city' WHERE address_format_id = 5;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country' , address_summary = '$city / $state / $postcode' WHERE address_format_id = 6 ;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$postcode $city $statebrackets$cr$country' , address_summary = '$postcode $city $statebrecket' WHERE address_format_id = 7;

###Add new address formats
INSERT INTO address_format VALUES (8,'$firstname $lastname$cr$streets$cr$city $state$cr$postcode$cr$country','$city $state / $postcode');
INSERT INTO address_format VALUES (9,'$firstname $lastname$cr$streets$cr$postcode$cr$city $state$cr$country','$postcode / $city / $state');
INSERT INTO address_format VALUES (10,'$firstname $lastname$cr$streets$cr$city $postcode$cr$state$cr$country','$city $postcode / $state');
INSERT INTO address_format VALUES (11,'$firstname $lastname$cr$streets$cr$postcode $city$cr$state$cr$country','$postcode $city / $state');
INSERT INTO address_format VALUES (12,'$firstname $lastname$cr$streets$cr$postcode$cr$city$cr$state$cr$country','$postcode / $city / $state');
INSERT INTO address_format VALUES (13,'$firstname $lastname$cr$streets$cr$city $postcode $state$cr$country',' $city $postcode $state');
INSERT INTO address_format VALUES (14,'$firstname $lastname$cr$streets$cr$city$cr$postcode $state$cr$country',' $city / $postcode $state');
INSERT INTO address_format VALUES (15,'$firstname $lastname$cr$streets$cr$city$cr$state $postcode$cr$country','$city / $state $postcode');
INSERT INTO address_format VALUES (16,'$firstname $lastname$cr$city$cr$streets$cr$postcode$cr$country','$city $street / $postcode');

### Update countries with new address formats use countries_iso_code_2 to match
UPDATE countries SET address_format_id = '2' WHERE countries_iso_code_2 IN ('AS', 'AU', 'CC', 'CN', 'CO', 'CW', 'CX', 'FM', 'GU', 'GY', 'HM', 'JP', 'KH', 'KN', 'KR', 'KY', 'LV', 'MH', 'MM', 'MP', 'NF', 'PK', 'PR', 'PW', 'SO', 'TT', 'UM', 'VI');
UPDATE countries SET address_format_id = '3' WHERE countries_iso_code_2 IN ('CU', 'HN', 'IT', 'LR', 'MX', 'TN', 'TR', 'VA');
UPDATE countries SET address_format_id = '4' WHERE countries_iso_code_2 IN ('AI', 'AQ', 'BD', 'BF', 'BH', 'BM', 'BQ', 'BS', 'BT', 'BZ', 'CK', 'ID', 'JO', 'KP', 'LB', 'LS', 'MN', 'MV', 'NP', 'NR', 'NU', 'NZ', 'SA', 'TK', 'TL', 'TW', 'VC', 'VG', 'ZM');
UPDATE countries SET address_format_id = '5' WHERE countries_iso_code_2 IN ('AD', 'AL', 'AM', 'AR', 'AX', 'AZ', 'BA', 'BG', 'CH', 'CL', 'CV', 'CY', 'CZ', 'DK', 'DO', 'DZ', 'EE', 'ET', 'FI', 'FO', 'FR', 'GA', 'GE', 'GF', 'GL', 'GN', 'GP', 'GQ', 'GR', 'GW', 'HR', 'HT', 'IL', 'IS', 'JM', 'KW', 'LA', 'LI', 'LT', 'LU', 'MA', 'MC', 'MD', 'ME', 'MG', 'MK', 'MQ', 'NC', 'NE', 'NO', 'PF', 'PL', 'PM', 'PS', 'PT', 'PY', 'RE', 'RO', 'SI', 'SJ', 'SK', 'SM', 'SN', 'SS', 'SY', 'TF', 'TJ', 'TM', 'UY', 'WF', 'YT');
UPDATE countries SET address_format_id = '6' WHERE countries_iso_code_2 IN ('AE', 'AF', 'EG', 'FK', 'GG', 'GI', 'GS', 'IE', 'IM', 'IN', 'IO', 'IR', 'JE', 'KE', 'KI', 'KZ', 'LK', 'MS', 'MT', 'PN', 'RS', 'RU', 'SB', 'SC', 'SH', 'SZ', 'TC', 'TG', 'TV', 'UA', 'UZ', 'ZA', 'ZW');
UPDATE countries SET address_format_id = '7' WHERE countries_iso_code_2 = 'ES';
UPDATE countries SET address_format_id = '8' WHERE countries_iso_code_2 IN ('BR', 'CR', 'GH', 'IQ', 'TH');
UPDATE countries SET address_format_id = '9' WHERE countries_iso_code_2 IN ('EC', 'NI', 'PE', 'SD');
UPDATE countries SET address_format_id = '10' WHERE countries_iso_code_2 = 'NG';
UPDATE countries SET address_format_id = '11' WHERE countries_iso_code_2 IN ('BY', 'GT', 'KG', 'MY' 'MZ', 'PA', 'SV', 'TZ');
UPDATE countries SET address_format_id = '12' WHERE countries_iso_code_2 = 'OM';
UPDATE countries SET address_format_id = '13' WHERE countries_iso_code_2 IN ('PG', 'VE');
UPDATE countries SET address_format_id = '14' WHERE countries_iso_code_2 = 'PH';
UPDATE countries SET address_format_id = '15' WHERE countries_iso_code_2 = 'BN';
UPDATE countries SET address_format_id = '15' WHERE countries_iso_code_2 = 'VN';
UPDATE countries SET address_format_id = '16' WHERE countries_iso_code_2 = 'HU';
################

#### Added in case was missed on upgrades.  also modified to allow for IgnoreDups in case someone had earlier version installed.
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Admin)?', 'REPORT_ALL_ERRORS_ADMIN', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', 10, 40, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Store)?', 'REPORT_ALL_ERRORS_STORE', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', 10, 41, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors: Backtrace on Notice Errors?', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE', 'No', 'Include backtrace information on &quot;Notice&quot; errors?  These are usually isolated to the identified file and the backtrace information just fills the logs. Default (<b>No</b>).', 10, 42, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\'),');
UPDATE configuration SET configuration_description = 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', set_function = 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),' WHERE configuration_key = 'REPORT_ALL_ERRORS_ADMIN';
UPDATE configuration SET configuration_description = 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', set_function = 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),' WHERE configuration_key = 'REPORT_ALL_ERRORS_STORE';
############


#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.8-dev', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.7->1.5.8-dev', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.8-dev', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.7->1.5.8-dev', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
