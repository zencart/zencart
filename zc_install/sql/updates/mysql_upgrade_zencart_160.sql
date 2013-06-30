#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.1 to v1.6.0
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2013 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version GIT: $Id: Author: DrByte  Tue Aug 28 16:03:47 2012 -0400 New in v1.6.0 $
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

UPDATE configuration set configuration_description = 'This should point to the folder specified in your DIR_FS_SQL_CACHE setting in your configure.php files.' WHERE configuration_key = 'SESSION_WRITE_DIRECTORY';
UPDATE configuration set configuration_description = 'Store the database queries in the page parse time log. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.' WHERE configuration_key = 'STORE_DB_TRANSACTIONS';

UPDATE configuration set configuration_title = 'Log Page Parse Time', configuration_description = 'Record (to a log file) the time it takes to parse a page' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Destination', configuration_description = 'Directory and filename of the page parse time log' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';
UPDATE configuration set configuration_title = 'Log Date Format', configuration_description = 'The date format' WHERE configuration_key = 'STORE_PARSE_DATE_TIME_FORMAT';
UPDATE configuration set configuration_title = 'Display The Page Parse Time', configuration_description = 'Display the page parse time on the bottom of each page<br />(Note: This DISPLAYS them. You do NOT need to LOG them to merely display them on your site.)' WHERE configuration_key = 'DISPLAY_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Database Queries', configuration_description = 'Record the database queries to files in the system /logs/ folder. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.' WHERE configuration_key = 'STORE_DB_TRANSACTIONS';
UPDATE configuration set sort_order = '1', configuration_description = 'Send out e-mails?<br>(Default state is ON.<br>Turn off to suppress ALL outgoing email messages from this store.)' WHERE configuration_key = 'SEND_EMAILS';
UPDATE configuration set sort_order = '2', configuration_description = 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is mostly obsolete and only used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.' WHERE configuration_key = 'EMAIL_TRANSPORT';
UPDATE configuration set configuration_description = 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.<br><br>Default: 25<br>Typical values are:<br>25 - normal unencrypted SMTP<br>587 - encrypted SMTP<br>465 - older MS SMTP port' WHERE configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT';

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES (NULL, 'Search Engines - Disable Indexing', 'ROBOTS_NOINDEX_MAINTENANCE_MODE', 'Normal', 'When in development it is sometimes desirable to discourage search engines from indexing your site. To do that, set this to Maintenance. This will cause a noindex,nofollow tag to be generated on all pages, thus discouraging search engines from indexing your pages until you set this back to Normal.<br>Default: Normal', 1, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'Normal\', \'Maintenance\'),');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Primary Source', 'CURRENCY_SERVER_PRIMARY', 'ecb', 'Where to request external currency updates from (Primary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Currency Exchange Rate: Secondary Source', 'CURRENCY_SERVER_BACKUP', 'boc', 'Where to request external currency updates from (Secondary source)<br><br>Additional sources can be installed via plugins.', '1', '55', 'zen_cfg_pull_down_exchange_rate_sources(', now());

ALTER TABLE sessions MODIFY sesskey varchar(255) NOT NULL default '';
ALTER TABLE whos_online MODIFY session_id varchar(255) NOT NULL default '';
ALTER TABLE admin_menus MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY main_page VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_params VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_profiles MODIFY profile_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages_to_profiles MODIFY page_key varchar(255) NOT NULL default '';
ALTER TABLE coupons ADD coupon_total TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE orders ADD order_weight FLOAT NOT NULL DEFAULT '0';

ALTER TABLE orders_products ADD products_weight float NOT NULL default '0';
ALTER TABLE orders_products ADD products_virtual tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products ADD product_is_always_free_shipping tinyint( 1 ) NOT NULL default '0';
ALTER TABLE orders_products_download ADD products_attributes_id int( 11 ) NOT NULL;

##@TODO
## COWOA CHANGES - Although need to allow for a current cowoa installation
ALTER TABLE customers ADD COLUMN COWOA_account tinyint(1) NOT NULL default 0;
ALTER TABLE orders ADD COLUMN COWOA_order tinyint(1) NOT NULL default 0;
INSERT INTO configuration_group VALUES (NULL, 'Guest Checkout', 'Set Checkout Without an Account', '100', '1');

#NEXT_X_ROWS_AS_ONE_COMMAND:4
SET @t1=0;
SELECT (@t1:=configuration_group_id) as t1 FROM configuration_group WHERE configuration_group_title = 'Guest Checkout';
INSERT INTO admin_pages VALUES ('configCOWOA','BOX_CONFIGURATION_COWOA','FILENAME_CONFIGURATION','gID=@t1:', 'configuration', 'Y', 31);
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES
(NULL, 'COWOA', 'COWOA_STATUS', 'false', 'Activate COWOA Checkout? <br />Set to True to allow a customer to checkout without an account.', @t1:, 10, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Enable Order Status', 'COWOA_ORDER_STATUS', 'false', 'Enable The Order Status Function of COWOA?<br />Set to True so that a Customer that uses COWOA will receive an E-Mail with instructions on how to view the status of their order.', @t1:, 11, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Enable E-Mail Only', 'COWOA_EMAIL_ONLY', 'false', 'Enable The E-Mail Order Function of COWOA?<br />Set to True so that a Customer that uses COWOA will only need to enter their E-Mail Address upon checkout if their Cart Balance is 0 (Free).', @t1:, 12, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),
(NULL, 'Enable Forced Logoff', 'COWOA_LOGOFF', 'false', 'Enable The Forced LogOff Function of COWOA?<br />Set to True so that a Customer that uses COWOA will be logged off automatically after a sucessfull checkout. If they are getting a file download, then they will have to wait for the Status E-Mail to arrive in order to download the file.', @t1:, 13, NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');

INSERT INTO query_builder ( query_id , query_category , query_name , query_description , query_string ) VALUES ( '', 'email,newsletters', 'Permanent Account Holders Only', 'Send email only to permanent account holders ', 'select customers_email_address, customers_firstname, customers_lastname from TABLE_CUSTOMERS where COWOA_account != 1 order by customers_lastname, customers_firstname, customers_email_address');

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
# Dumping data for table 'dashboard_widgets'
#

INSERT INTO dashboard_widgets (widget_key, widget_group, widget_status) VALUES
('general-statistics', 'general-statistics', 1),
('order-summary', 'order-statistics', 1);

#
# Dumping data for table 'dashboard_widgets_description'
#

INSERT INTO dashboard_widgets_description (widget_key, widget_name, widget_description, language_id) VALUES
('general-statistics', 'General Statistics', '', 1),
('order-summary', 'Order Summary', '', 1);

#
# Dumping data for table 'dashboard_widgets_to_users'
#

INSERT INTO dashboard_widgets_to_users (widget_key, admin_id, widget_row, widget_column) VALUES
('general-statistics', 1, 0, 0),
('order-summary', 1, 0, 1);

INSERT INTO dashboard_widgets_groups (widget_group, language_id, widget_group_name) VALUES
('general-statistics', 1, 'General Statistics'),
('order-statistics', 1, 'Order Statistics');

## CHANGE-346 - Fix outdated language in configuration menu help texts
## CHANGE-411 increase size of fileds in admin profile related tables
## CHANGE-367 - Dashboard Widgets for 1.6 Admin Home Page (including ajax infrastructure)…

#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='6.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.1->1.6.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='6.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.1->1.6.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT

