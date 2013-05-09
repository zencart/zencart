#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.1 to v1.6.0
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2012 Zen Cart Development Team
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

ALTER TABLE sessions MODIFY sesskey varchar(255) NOT NULL default '';
ALTER TABLE whos_online MODIFY session_id varchar(255) NOT NULL default '';
ALTER TABLE admin_menus MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY main_page VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY page_params VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages MODIFY menu_key VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_profiles MODIFY profile_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE admin_pages_to_profiles MODIFY page_key varchar(255) NOT NULL default '';


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

