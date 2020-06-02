#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.4 to v1.5.5
# *
# * @access private
# * @copyright Copyright 2003-2020 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Scott C Wilson 2019 May 31 Modified in v1.5.7 $
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

UPDATE configuration set configuration_group_id = 6 where configuration_key in ('PRODUCTS_OPTIONS_TYPE_SELECT', 'UPLOAD_PREFIX', 'TEXT_PREFIX');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Product option type Select', 'PRODUCTS_OPTIONS_TYPE_SELECT', '0', 'The number representing the Select type of product option.', 6, NULL, now(), now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Upload prefix', 'UPLOAD_PREFIX', 'upload_', 'Prefix used to differentiate between upload options and other options', 6, NULL, now(), now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Text prefix', 'TEXT_PREFIX', 'txt_', 'Prefix used to differentiate between text option values and other option values', 6, NULL, now(), now(), NULL, NULL);

UPDATE countries set countries_name = 'Åland Islands' where countries_iso_code_3 = 'ALA';
UPDATE countries set countries_name = 'Réunion' where countries_iso_code_3 = 'REU';
UPDATE countries set countries_name = "Côte d'Ivoire" where countries_iso_code_3 = 'CIV';
UPDATE countries set countries_name = 'Bonaire, Sint Eustatius and Saba', countries_iso_code_2 = 'BQ', countries_iso_code_3 = 'BES' WHERE countries_iso_code_3 = 'ANT';
INSERT INTO countries (countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES ('Curaçao','CW','CUW','1');
INSERT INTO countries (countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES ('Sint Maarten (Dutch part)','SX','SXM','1');

UPDATE configuration SET configuration_title='Credit Card Enable Status - Debit', configuration_key = 'CC_ENABLED_DEBIT', configuration_value ='0', configuration_description='Accept Debit Cards 0= off 1= on<br>NOTE: This is not deeply integrated at this time, and this setting may be redundant if your payment modules do not yet specifically have code to honour this switch.', date_added=now() WHERE configuration_key='CC_ENABLED_SWITCH';

UPDATE configuration set configuration_title = 'Enable HTML Emails?', configuration_description = 'Send emails in HTML format if recipient has enabled it in their preferences.' WHERE configuration_key = 'EMAIL_USE_HTML';
UPDATE configuration set configuration_title = 'Email Admin Format?', configuration_description = 'Please select the Admin extra email format (Note: Enable HTML Emails must be on for HTML option to work)' WHERE configuration_key = 'ADMIN_EXTRA_EMAIL_FORMAT';

UPDATE configuration SET configuration_title='Prev/Next Navigation Page Links (Desktop)', configuration_description='Number of numbered pagination links to display.' WHERE configuration_key = 'MAX_DISPLAY_PAGE_LINKS';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Prev/Next Navigation Page Links (Mobile)', 'MAX_DISPLAY_PAGE_LINKS_MOBILE', '3', 'Number of numbered pagination links to display on Mobile devices (assuming your template supports mobile-specific settings)', '3', '3', now());

UPDATE configuration set sort_order = '1', configuration_description = 'Send out e-mails?<br>Normally this is set to true.<br>Set to false to suppress ALL outgoing email messages from this store, such as when working with a test copy of your store offline.' WHERE configuration_key = 'SEND_EMAILS';
UPDATE configuration set sort_order = '2', configuration_description = 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is mostly obsolete and only used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.' WHERE configuration_key = 'EMAIL_TRANSPORT';
UPDATE configuration set configuration_description = 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.<br><br>Default: 25<br>Typical values are:<br>25 - normal unencrypted SMTP<br>587 - encrypted SMTP<br>465 - older MS SMTP port' WHERE configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Primary Source', 'CURRENCY_SERVER_PRIMARY', 'ecb', 'Where to request external currency updates from (Primary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Secondary Source', 'CURRENCY_SERVER_BACKUP', 'boc', 'Where to request external currency updates from (Secondary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
DELETE FROM configuration where configuration_key = 'PHPBB_LINKS_ENABLED' && configuration_value != 'true';

UPDATE configuration_group SET configuration_group_description = 'Define Pages Options Settings' where configuration_group_title = 'Define Page Status';


ALTER TABLE paypal_payment_status_history MODIFY pending_reason varchar(32) default NULL;
ALTER TABLE coupons_description MODIFY coupon_name VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE orders MODIFY shipping_method VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY COLUMN pwd_last_change_date datetime NOT NULL default '0001-01-01 00:00:00', MODIFY COLUMN last_modified datetime NOT NULL default '0001-01-01 00:00:00', MODIFY COLUMN last_login_date datetime NOT NULL default '0001-01-01 00:00:00', MODIFY COLUMN last_failed_attempt datetime NOT NULL default '0001-01-01 00:00:00';
UPDATE admin SET pwd_last_change_date='0001-01-01' where pwd_last_change_date < '0001-01-01';
UPDATE admin SET last_modified='0001-01-01' where last_modified < '0001-01-01';
UPDATE admin SET last_login_date='0001-01-01' where last_login_date < '0001-01-01';
UPDATE admin SET last_failed_attempt='0001-01-01' where last_failed_attempt < '0001-01-01';
ALTER TABLE admin MODIFY admin_pass VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass1 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass2 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY prev_pass3 VARCHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE admin MODIFY reset_token VARCHAR( 255 ) NOT NULL DEFAULT '';

UPDATE customers SET customers_dob='0001-01-01' where customers_dob < '0001-01-01';
ALTER TABLE customers MODIFY customers_password VARCHAR( 255 ) NOT NULL DEFAULT '';

ALTER TABLE sessions MODIFY sesskey varchar(255) NOT NULL default '';
ALTER TABLE whos_online MODIFY session_id varchar(255) NOT NULL default '';
ALTER TABLE admin_menus MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY main_page VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_params VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_profiles MODIFY profile_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages_to_profiles MODIFY page_key varchar(255) NOT NULL default '';

ALTER TABLE currencies MODIFY symbol_left VARCHAR(32) DEFAULT NULL;
ALTER TABLE currencies MODIFY symbol_right VARCHAR(32) DEFAULT NULL;

ALTER TABLE counter_history MODIFY startdate char(8) NOT NULL default '';

UPDATE query_builder set query_string = 'select max(o.date_purchased) as date_purchased, c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id AND c.customers_newsletter = 1 GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname HAVING max(o.date_purchased) <= subdate(now(),INTERVAL 3 MONTH) ORDER BY c.customers_lastname, c.customers_firstname ASC' where query_name='Dormant Customers (>3months) (Subscribers)';
UPDATE query_builder set query_string = 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o where c.customers_newsletter = \'1\' AND c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC' where query_name='Active customers in past 3 months (Subscribers)';
UPDATE query_builder set query_string = 'select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address, c.customers_lastname, c.customers_firstname order by c.customers_lastname, c.customers_firstname ASC' where query_name='Active customers in past 3 months (Regardless of subscription status)';

ALTER TABLE products_description MODIFY products_id int(11) NOT NULL;


# Insert a default profile for managing orders, as a built-in example of profile functionality
INSERT INTO admin_profiles (profile_name) values ('Order Processing');
SET @profile_id=last_insert_id();
INSERT INTO admin_pages_to_profiles (profile_id, page_key) VALUES
(@profile_id, 'customers'),
(@profile_id, 'orders'),
(@profile_id, 'invoice'),
(@profile_id, 'packingslip'),
(@profile_id, 'paypal'),
(@profile_id, 'currencies'),
(@profile_id, 'reportCustomers'),
(@profile_id, 'reportLowStock'),
(@profile_id, 'reportProductsSold'),
(@profile_id, 'reportProductsViewed'),
(@profile_id, 'reportReferrals'),
(@profile_id, 'gvMail'),
(@profile_id, 'gvQueue'),
(@profile_id, 'gvSent'),
(@profile_id, 'whosOnline');

#############

INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES
('responsive_classic', 'banner_box.php', 1, 0, 300, 1, 127),
('responsive_classic', 'banner_box2.php', 1, 1, 15, 1, 15),
('responsive_classic', 'banner_box_all.php', 1, 1, 5, 0, 0),
('responsive_classic', 'best_sellers.php', 1, 1, 30, 70, 1),
('responsive_classic', 'categories.php', 1, 0, 10, 10, 1),
('responsive_classic', 'currencies.php', 0, 1, 80, 60, 0),
('responsive_classic', 'document_categories.php', 1, 0, 0, 0, 0),
('responsive_classic', 'ezpages.php', 1, 1, -1, 2, 1),
('responsive_classic', 'featured.php', 1, 0, 45, 0, 0),
('responsive_classic', 'information.php', 1, 0, 50, 40, 1),
('responsive_classic', 'languages.php', 0, 1, 70, 50, 0),
('responsive_classic', 'manufacturers.php', 1, 0, 30, 20, 1),
('responsive_classic', 'manufacturer_info.php', 1, 1, 35, 95, 1),
('responsive_classic', 'more_information.php', 1, 0, 200, 200, 1),
('responsive_classic', 'music_genres.php', 1, 1, 0, 0, 0),
('responsive_classic', 'order_history.php', 1, 1, 0, 0, 0),
('responsive_classic', 'product_notifications.php', 1, 1, 55, 85, 1),
('responsive_classic', 'record_companies.php', 1, 1, 0, 0, 0),
('responsive_classic', 'reviews.php', 1, 0, 40, 0, 0),
('responsive_classic', 'search.php', 1, 1, 10, 0, 0),
('responsive_classic', 'search_header.php', 0, 0, 0, 0, 1),
('responsive_classic', 'shopping_cart.php', 1, 1, 20, 30, 1),
('responsive_classic', 'specials.php', 1, 1, 45, 0, 0),
('responsive_classic', 'whats_new.php', 1, 0, 20, 0, 0),
('responsive_classic', 'whos_online.php', 1, 1, 200, 200, 1);

#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.5f', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.5.5f', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.5.5f', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT

