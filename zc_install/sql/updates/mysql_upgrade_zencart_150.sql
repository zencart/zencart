#
# * This SQL script upgrades the core Zen Cart database structure from v1.3.9 to v1.5.0
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2016 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
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

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Telephone - Customer Service', 'STORE_TELEPHONE_CUSTSERVICE', '', 'Enter a telephone number for customers to reach your Customer Service department. This number may be sent as part of payment transaction details.', 1, 7, now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('login mode https', 'SSLPWSTATUSCHECK', '', 'System setting. Do not edit.', 6, 99, now());

UPDATE configuration set configuration_value = '900', configuration_description = 'Enter the time in seconds.<br />Max allowed is 900 for PCI Compliance Reasons.<br /> Default=900<br />Example: 900= 15 min <br /><br />Note: Too few seconds can result in timeout issues when adding/editing products', set_function = 'zen_cfg_select_option(array(\'900\', \'600\', \'300\'), ' where configuration_key = 'SESSION_TIMEOUT_ADMIN';
UPDATE configuration set configuration_title='CURL Proxy Address', configuration_value = '', configuration_group_id = 6, configuration_description = 'If you have a hosting service that requires use of a proxy to talk to external sites via cURL, enter their proxy address here.<br />format: address:port<br />ie: 127.0.0.1:3128' where configuration_key = 'CURL_PROXY_SERVER_DETAILS';
UPDATE configuration set configuration_title='CURL Proxy Status', configuration_value = 'False', configuration_group_id = 6 where configuration_key = 'CURL_PROXY_REQUIRED';
UPDATE configuration set configuration_description = 'Option Names and Values Global Add, Copy and Delete Features Status<br />0= Hide Features<br />1= Show Features<br />(Default=1)' where configuration_key = 'OPTION_NAMES_VALUES_GLOBAL_STATUS';
UPDATE configuration set sort_order=7 where configuration_key = 'STORE_NAME_ADDRESS';
UPDATE configuration set configuration_title='Admin Search Results Per Page', configuration_description='Number of products to list on an Admin search result page' where configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS';

ALTER TABLE sessions CHANGE COLUMN sesskey sesskey varchar(64) NOT NULL default '';
ALTER TABLE admin_activity_log CHANGE COLUMN log_id log_id bigint(15) NOT NULL auto_increment;
ALTER TABLE admin_activity_log ADD COLUMN flagged tinyint NOT NULL default '0';
ALTER TABLE admin_activity_log ADD COLUMN attention varchar(255) NOT NULL default '';
ALTER TABLE admin_activity_log ADD COLUMN gzpost mediumblob ;
ALTER TABLE admin_activity_log DROP INDEX page_accessed;
ALTER TABLE admin_activity_log DROP INDEX access_date;
ALTER TABLE admin_activity_log ADD INDEX idx_page_accessed_zen (page_accessed);
ALTER TABLE admin_activity_log ADD INDEX idx_access_date_zen (access_date);
ALTER TABLE admin_activity_log ADD INDEX idx_flagged_zen (flagged);
ALTER TABLE admin_activity_log CHANGE COLUMN ip_address ip_address varchar(20) NOT NULL default '';

ALTER TABLE whos_online CHANGE COLUMN ip_address ip_address varchar(20) NOT NULL default '';

ALTER TABLE admin ADD COLUMN admin_profile int(11) NOT NULL default '0' AFTER admin_email;
ALTER TABLE admin ADD INDEX idx_admin_profile_zen (admin_profile);
ALTER TABLE admin ADD COLUMN prev_pass1 varchar(40) NOT NULL default '';
ALTER TABLE admin ADD COLUMN prev_pass2 varchar(40) NOT NULL default '';
ALTER TABLE admin ADD COLUMN prev_pass3 varchar(40) NOT NULL default '';
ALTER TABLE admin ADD COLUMN pwd_last_change_date datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin ADD COLUMN reset_token varchar(60) NOT NULL default '';
ALTER TABLE admin ADD COLUMN last_modified datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin ADD COLUMN last_login_date datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin ADD COLUMN last_login_ip varchar(15) NOT NULL default '';
ALTER TABLE admin ADD COLUMN failed_logins smallint(4) unsigned NOT NULL default '0';
ALTER TABLE admin ADD COLUMN lockout_expires int(11) NOT NULL default '0';
ALTER TABLE admin ADD COLUMN last_failed_attempt datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE admin ADD COLUMN last_failed_ip varchar(15) NOT NULL default '';
UPDATE admin set pwd_last_change_date = now();
ALTER TABLE admin DROP COLUMN admin_level;

#
# Table structure for table 'admin_menu'
#

DROP TABLE IF EXISTS admin_menus;
CREATE TABLE admin_menus (
  menu_key VARCHAR(32) NOT NULL DEFAULT '',
  language_key VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT(11) NOT NULL DEFAULT 0,
  UNIQUE KEY menu_key (menu_key)
);

# --------------------------------------------------------

#
# Table structure for table 'admin_pages'
#

DROP TABLE IF EXISTS admin_pages;
CREATE TABLE admin_pages (
  page_key VARCHAR(32) NOT NULL DEFAULT '',
  language_key VARCHAR(255) NOT NULL DEFAULT '',
  main_page varchar(64) NOT NULL default '',
  page_params varchar(64) NOT NULL default '',
  menu_key varchar(32) NOT NULL default '',
  display_on_menu char(1) NOT NULL default 'N',
  sort_order int(11) NOT NULL default 0,
  UNIQUE KEY page_key (page_key)
);

# --------------------------------------------------------

#
# Table structure for table 'admin_profiles'
#

DROP TABLE IF EXISTS admin_profiles;
CREATE TABLE admin_profiles (
  profile_id int(11) NOT NULL AUTO_INCREMENT,
  profile_name varchar(64) NOT NULL default '',
  PRIMARY KEY (profile_id)
);

# --------------------------------------------------------

#
# Table structure for table 'admin_pages_to_profiles'
#

DROP TABLE IF EXISTS admin_pages_to_profiles;
CREATE TABLE admin_pages_to_profiles (
  profile_id int(11) NOT NULL default '0',
  page_key varchar(32) NOT NULL default '',
  UNIQUE KEY profile_page (profile_id, page_key),
  UNIQUE KEY page_profile (page_key, profile_id));

# Insert default data into admin profiles table
INSERT INTO admin_profiles (profile_id, profile_name) VALUES (1, 'Superuser');

#set all users as superuser for now; can be changed manually as needed
UPDATE admin SET admin_profile = 1;

# Insert default data into admin_menus table
INSERT INTO admin_menus (menu_key, language_key, sort_order)
VALUES ('configuration', 'BOX_HEADING_CONFIGURATION', 1),
       ('catalog', 'BOX_HEADING_CATALOG', 2),
       ('modules', 'BOX_HEADING_MODULES', 3),
       ('customers', 'BOX_HEADING_CUSTOMERS', 4),
       ('taxes', 'BOX_HEADING_LOCATION_AND_TAXES', 5),
       ('localization', 'BOX_HEADING_LOCALIZATION', 6),
       ('reports', 'BOX_HEADING_REPORTS', 7),
       ('tools', 'BOX_HEADING_TOOLS', 8),
       ('gv', 'BOX_HEADING_GV_ADMIN', 9),
       ('access', 'BOX_HEADING_ADMIN_ACCESS', 10),
       ('extras', 'BOX_HEADING_EXTRAS', 11);

# Insert data into admin_pages table
INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('configMyStore', 'BOX_CONFIGURATION_MY_STORE', 'FILENAME_CONFIGURATION', 'gID=1', 'configuration', 'Y', 1),
       ('configMinimumValues', 'BOX_CONFIGURATION_MINIMUM_VALUES', 'FILENAME_CONFIGURATION', 'gID=2', 'configuration', 'Y', 2),
       ('configMaximumValues', 'BOX_CONFIGURATION_MAXIMUM_VALUES', 'FILENAME_CONFIGURATION', 'gID=3', 'configuration', 'Y', 3),
       ('configImages', 'BOX_CONFIGURATION_IMAGES', 'FILENAME_CONFIGURATION', 'gID=4', 'configuration', 'Y', 4),
       ('configCustomerDetails', 'BOX_CONFIGURATION_CUSTOMER_DETAILS', 'FILENAME_CONFIGURATION', 'gID=5', 'configuration', 'Y', 5),
       ('configShipping', 'BOX_CONFIGURATION_SHIPPING_PACKAGING', 'FILENAME_CONFIGURATION', 'gID=7', 'configuration', 'Y', 6),
       ('configProductListing', 'BOX_CONFIGURATION_PRODUCT_LISTING', 'FILENAME_CONFIGURATION', 'gID=8', 'configuration', 'Y', 7),
       ('configStock', 'BOX_CONFIGURATION_STOCK', 'FILENAME_CONFIGURATION', 'gID=9', 'configuration', 'Y', 8),
       ('configLogging', 'BOX_CONFIGURATION_LOGGING', 'FILENAME_CONFIGURATION', 'gID=10', 'configuration', 'Y', 9),
       ('configEmail', 'BOX_CONFIGURATION_EMAIL_OPTIONS', 'FILENAME_CONFIGURATION', 'gID=12', 'configuration', 'Y', 10),
       ('configAttributes', 'BOX_CONFIGURATION_ATTRIBUTE_OPTIONS', 'FILENAME_CONFIGURATION', 'gID=13', 'configuration', 'Y', 11),
       ('configGzipCompression', 'BOX_CONFIGURATION_GZIP_COMPRESSION', 'FILENAME_CONFIGURATION', 'gID=14', 'configuration', 'Y', 12),
       ('configSessions', 'BOX_CONFIGURATION_SESSIONS', 'FILENAME_CONFIGURATION', 'gID=15', 'configuration', 'Y', 13),
       ('configRegulations', 'BOX_CONFIGURATION_REGULATIONS', 'FILENAME_CONFIGURATION', 'gID=11', 'configuration', 'Y', 14),
       ('configGvCoupons', 'BOX_CONFIGURATION_GV_COUPONS', 'FILENAME_CONFIGURATION', 'gID=16', 'configuration', 'Y', 15),
       ('configCreditCards', 'BOX_CONFIGURATION_CREDIT_CARDS', 'FILENAME_CONFIGURATION', 'gID=17', 'configuration', 'Y', 16),
       ('configProductInfo', 'BOX_CONFIGURATION_PRODUCT_INFO', 'FILENAME_CONFIGURATION', 'gID=18', 'configuration', 'Y', 17),
       ('configLayoutSettings', 'BOX_CONFIGURATION_LAYOUT_SETTINGS', 'FILENAME_CONFIGURATION', 'gID=19', 'configuration', 'Y', 18),
       ('configWebsiteMaintenance', 'BOX_CONFIGURATION_WEBSITE_MAINTENANCE', 'FILENAME_CONFIGURATION', 'gID=20', 'configuration', 'Y', 19),
       ('configNewListing', 'BOX_CONFIGURATION_NEW_LISTING', 'FILENAME_CONFIGURATION', 'gID=21', 'configuration', 'Y', 20),
       ('configFeaturedListing', 'BOX_CONFIGURATION_FEATURED_LISTING', 'FILENAME_CONFIGURATION', 'gID=22', 'configuration', 'Y', 21),
       ('configAllListing', 'BOX_CONFIGURATION_ALL_LISTING', 'FILENAME_CONFIGURATION', 'gID=23', 'configuration', 'Y', 22),
       ('configIndexListing', 'BOX_CONFIGURATION_INDEX_LISTING', 'FILENAME_CONFIGURATION', 'gID=24', 'configuration', 'Y', 23),
       ('configDefinePageStatus', 'BOX_CONFIGURATION_DEFINE_PAGE_STATUS', 'FILENAME_CONFIGURATION', 'gID=25', 'configuration', 'Y', 24),
       ('configEzPagesSettings', 'BOX_CONFIGURATION_EZPAGES_SETTINGS', 'FILENAME_CONFIGURATION', 'gID=30', 'configuration', 'Y', 25),
       ('categories', 'BOX_CATALOG_CATEGORIES_PRODUCTS', 'FILENAME_CATEGORIES', '', 'catalog', 'Y', 1),
       ('productTypes', 'BOX_CATALOG_PRODUCT_TYPES', 'FILENAME_PRODUCT_TYPES', '', 'catalog', 'Y', 2),
       ('priceManager', 'BOX_CATALOG_PRODUCTS_PRICE_MANAGER', 'FILENAME_PRODUCTS_PRICE_MANAGER', '', 'catalog', 'Y', 3),
       ('optionNames', 'BOX_CATALOG_CATEGORIES_OPTIONS_NAME_MANAGER', 'FILENAME_OPTIONS_NAME_MANAGER', '', 'catalog', 'Y', 4),
       ('optionValues', 'BOX_CATALOG_CATEGORIES_OPTIONS_VALUES_MANAGER', 'FILENAME_OPTIONS_VALUES_MANAGER', '', 'catalog', 'Y', 5),
       ('attributes', 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_CONTROLLER', 'FILENAME_ATTRIBUTES_CONTROLLER', '', 'catalog', 'Y', 6),
       ('downloads', 'BOX_CATALOG_CATEGORIES_ATTRIBUTES_DOWNLOADS_MANAGER', 'FILENAME_DOWNLOADS_MANAGER', '', 'catalog', 'Y', 7),
       ('optionNameSorter', 'BOX_CATALOG_PRODUCT_OPTIONS_NAME', 'FILENAME_PRODUCTS_OPTIONS_NAME', '', 'catalog', 'Y', 8),
       ('optionValueSorter', 'BOX_CATALOG_PRODUCT_OPTIONS_VALUES', 'FILENAME_PRODUCTS_OPTIONS_VALUES', '', 'catalog', 'Y', 9),
       ('manufacturers', 'BOX_CATALOG_MANUFACTURERS', 'FILENAME_MANUFACTURERS', '', 'catalog', 'Y', 10),
       ('reviews', 'BOX_CATALOG_REVIEWS', 'FILENAME_REVIEWS', '', 'catalog', 'Y', 11),
       ('specials', 'BOX_CATALOG_SPECIALS', 'FILENAME_SPECIALS', '', 'catalog', 'Y', 12),
       ('featured', 'BOX_CATALOG_FEATURED', 'FILENAME_FEATURED', '', 'catalog', 'Y', 13),
       ('salemaker', 'BOX_CATALOG_SALEMAKER', 'FILENAME_SALEMAKER', '', 'catalog', 'Y', 14),
       ('productsExpected', 'BOX_CATALOG_PRODUCTS_EXPECTED', 'FILENAME_PRODUCTS_EXPECTED', '', 'catalog', 'Y', 15),
       ('product', 'BOX_CATALOG_PRODUCT', 'FILENAME_PRODUCT', '', 'catalog', 'N', 16),
       ('productsToCategories', 'BOX_CATALOG_PRODUCTS_TO_CATEGORIES', 'FILENAME_PRODUCTS_TO_CATEGORIES', '', 'catalog', 'N', 17),
       ('payment', 'BOX_MODULES_PAYMENT', 'FILENAME_MODULES', 'set=payment', 'modules', 'Y', 1),
       ('shipping', 'BOX_MODULES_SHIPPING', 'FILENAME_MODULES', 'set=shipping', 'modules', 'Y', 2),
       ('orderTotal', 'BOX_MODULES_ORDER_TOTAL', 'FILENAME_MODULES', 'set=ordertotal', 'modules', 'Y', 3),
       ('customers', 'BOX_CUSTOMERS_CUSTOMERS', 'FILENAME_CUSTOMERS', '', 'customers', 'Y', 1),
       ('orders', 'BOX_CUSTOMERS_ORDERS', 'FILENAME_ORDERS', '', 'customers', 'Y', 2),
       ('groupPricing', 'BOX_CUSTOMERS_GROUP_PRICING', 'FILENAME_GROUP_PRICING', '', 'customers', 'Y', 3),
       ('paypal', 'BOX_CUSTOMERS_PAYPAL', 'FILENAME_PAYPAL', '', 'customers', 'Y', 4),
       ('invoice', 'BOX_CUSTOMERS_INVOICE', 'FILENAME_ORDERS_INVOICE', '', 'customers', 'N', 5),
       ('packingslip', 'BOX_CUSTOMERS_PACKING_SLIP', 'FILENAME_ORDERS_PACKINGSLIP', '', 'customers', 'N', 6),
       ('linkpointReview', 'BOX_CUSTOMERS_LINKPOINT_REVIEW', 'FILENAME_LINKPOINT_REVIEW', '', 'customers', 'Y', 7),
       ('countries', 'BOX_TAXES_COUNTRIES', 'FILENAME_COUNTRIES', '', 'taxes', 'Y', 1),
       ('zones', 'BOX_TAXES_ZONES', 'FILENAME_ZONES', '', 'taxes', 'Y', 2),
       ('geoZones', 'BOX_TAXES_GEO_ZONES', 'FILENAME_GEO_ZONES', '', 'taxes', 'Y', 3),
       ('taxClasses', 'BOX_TAXES_TAX_CLASSES', 'FILENAME_TAX_CLASSES', '', 'taxes', 'Y', 4),
       ('taxRates', 'BOX_TAXES_TAX_RATES', 'FILENAME_TAX_RATES', '', 'taxes', 'Y', 5),
       ('currencies', 'BOX_LOCALIZATION_CURRENCIES', 'FILENAME_CURRENCIES', '', 'localization', 'Y', 1),
       ('languages', 'BOX_LOCALIZATION_LANGUAGES', 'FILENAME_LANGUAGES', '', 'localization', 'Y', 2),
       ('ordersStatus', 'BOX_LOCALIZATION_ORDERS_STATUS', 'FILENAME_ORDERS_STATUS', '', 'localization', 'Y', 3),
       ('reportCustomers', 'BOX_REPORTS_ORDERS_TOTAL', 'FILENAME_STATS_CUSTOMERS', '', 'reports', 'Y', 1),
       ('reportReferrals', 'BOX_REPORTS_CUSTOMERS_REFERRALS', 'FILENAME_STATS_CUSTOMERS_REFERRALS', '', 'reports', 'Y', 2),
       ('reportLowStock', 'BOX_REPORTS_PRODUCTS_LOWSTOCK', 'FILENAME_STATS_PRODUCTS_LOWSTOCK', '', 'reports', 'Y', 3),
       ('reportProductsSold', 'BOX_REPORTS_PRODUCTS_PURCHASED', 'FILENAME_STATS_PRODUCTS_PURCHASED', '', 'reports', 'Y', 4),
       ('reportProductsViewed', 'BOX_REPORTS_PRODUCTS_VIEWED', 'FILENAME_STATS_PRODUCTS_VIEWED', '', 'reports', 'Y', 5),
       ('templateSelect', 'BOX_TOOLS_TEMPLATE_SELECT', 'FILENAME_TEMPLATE_SELECT', '', 'tools', 'Y', 1),
       ('layoutController', 'BOX_TOOLS_LAYOUT_CONTROLLER', 'FILENAME_LAYOUT_CONTROLLER', '', 'tools', 'Y', 2),
       ('banners', 'BOX_TOOLS_BANNER_MANAGER', 'FILENAME_BANNER_MANAGER', '', 'tools', 'Y', 3),
       ('mail', 'BOX_TOOLS_MAIL', 'FILENAME_MAIL', '', 'tools', 'Y', 4),
       ('newsletters', 'BOX_TOOLS_NEWSLETTER_MANAGER', 'FILENAME_NEWSLETTERS', '', 'tools', 'Y', 5),
       ('server', 'BOX_TOOLS_SERVER_INFO', 'FILENAME_SERVER_INFO', '', 'tools', 'Y', 6),
       ('whosOnline', 'BOX_TOOLS_WHOS_ONLINE', 'FILENAME_WHOS_ONLINE', '', 'tools', 'Y', 7),
       ('storeManager', 'BOX_TOOLS_STORE_MANAGER', 'FILENAME_STORE_MANAGER', '', 'tools', 'Y', 9),
       ('developersToolKit', 'BOX_TOOLS_DEVELOPERS_TOOL_KIT', 'FILENAME_DEVELOPERS_TOOL_KIT', '', 'tools', 'Y', 10),
       ('ezpages', 'BOX_TOOLS_EZPAGES', 'FILENAME_EZPAGES_ADMIN', '', 'tools', 'Y', 11),
       ('definePagesEditor', 'BOX_TOOLS_DEFINE_PAGES_EDITOR', 'FILENAME_DEFINE_PAGES_EDITOR', '', 'tools', 'Y', 12),
       ('sqlPatch', 'BOX_TOOLS_SQLPATCH', 'FILENAME_SQLPATCH', '', 'tools', 'Y', 13),
       ('couponAdmin', 'BOX_COUPON_ADMIN', 'FILENAME_COUPON_ADMIN', '', 'gv', 'Y', 1),
       ('couponRestrict', 'BOX_COUPON_RESTRICT', 'FILENAME_COUPON_RESTRICT', '', 'gv', 'N', 1),
       ('gvQueue', 'BOX_GV_ADMIN_QUEUE', 'FILENAME_GV_QUEUE', '', 'gv', 'Y', 2),
       ('gvMail', 'BOX_GV_ADMIN_MAIL', 'FILENAME_GV_MAIL', '', 'gv', 'Y', 3),
       ('gvSent', 'BOX_GV_ADMIN_SENT', 'FILENAME_GV_SENT', '', 'gv', 'Y', 4),
       ('profiles', 'BOX_ADMIN_ACCESS_PROFILES', 'FILENAME_PROFILES', '', 'access', 'Y', 1),
       ('users', 'BOX_ADMIN_ACCESS_USERS', 'FILENAME_USERS', '', 'access', 'Y', 2),
       ('pageRegistration', 'BOX_ADMIN_ACCESS_PAGE_REGISTRATION', 'FILENAME_ADMIN_PAGE_REGISTRATION', '', 'access', 'Y', 3),
       ('adminlogs', 'BOX_ADMIN_ACCESS_LOGS', 'FILENAME_ADMIN_ACTIVITY', '', 'access', 'Y', 4),
       ('recordArtists', 'BOX_CATALOG_RECORD_ARTISTS', 'FILENAME_RECORD_ARTISTS', '', 'extras', 'Y', 1),
       ('recordCompanies', 'BOX_CATALOG_RECORD_COMPANY', 'FILENAME_RECORD_COMPANY', '', 'extras', 'Y', 2),
       ('musicGenre', 'BOX_CATALOG_MUSIC_GENRE', 'FILENAME_MUSIC_GENRE', '', 'extras', 'Y', 3),
       ('mediaManager', 'BOX_CATALOG_MEDIA_MANAGER', 'FILENAME_MEDIA_MANAGER', '', 'extras', 'Y', 4),
       ('mediaTypes', 'BOX_CATALOG_MEDIA_TYPES', 'FILENAME_MEDIA_TYPES', '', 'extras', 'Y', 5);

# Clean up old US Armed Forces zones
update address_book set entry_zone_id = (
select zone_id
FROM zones where zone_country_id = 223 and zone_code = 'AE') where entry_zone_id in (
select zone_id
FROM zones where zone_country_id = 223 and zone_code
in ('AF', 'AM'));
update address_book set entry_zone_id = (
select zone_id
FROM zones where zone_country_id = 223 and zone_code = 'AA') where entry_zone_id in (
select zone_id
FROM zones where zone_country_id = 223 and zone_code = 'AC');
delete from zones where zone_country_id = 223 and zone_code in ('AF', 'AM', 'AC');


#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.9->1.5.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.9->1.5.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT


## Country ISO changes (intended to be at end of this script in case of errors which should not prevent teh rest of the script from executing properly.)
INSERT IGNORE INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (241,'Palestinian Territory','PS','PSE','1');
INSERT IGNORE INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (242,'Montenegro','ME','MNE','1');
INSERT IGNORE INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (243,'Guernsey','GG','GGY','1');
INSERT IGNORE INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (244,'Isle of Man','IM','IMN','1');
INSERT IGNORE INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (245,'Jersey','JE','JEY','1');
