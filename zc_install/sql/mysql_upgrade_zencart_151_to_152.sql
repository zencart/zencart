#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.1 to v1.5.2
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2013 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version GIT: $Id: Author: DrByte  Tue Aug 28 16:03:47 2012 -0400 New in v1.5.1 $
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
TRUNCATE TABLE sessions;

ALTER TABLE sessions MODIFY COLUMN sesskey varchar(255) NOT NULL default '';
ALTER TABLE whos_online MODIFY COLUMN session_id varchar(255) NOT NULL default '';

UPDATE configuration SET configuration_description = 'Store the database queries in the page parse time log. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.' WHERE configuration_key = 'STORE_DB_TRANSACTIONS';
UPDATE configuration SET configuration_description = 'This should point to the folder specified in your DIR_FS_SQL_CACHE setting in your configure.php files.' WHERE configuration_key = 'SESSION_WRITE_DIRECTORY';

UPDATE configuration set configuration_title = 'Log Page Parse Time', configuration_description = 'Record (to a log file) the time it takes to parse a page' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Destination', configuration_description = 'Directory and filename of the page parse time log' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';
UPDATE configuration set configuration_title = 'Log Date Format', configuration_description = 'The date format' WHERE configuration_key = 'STORE_PARSE_DATE_TIME_FORMAT';
UPDATE configuration set configuration_title = 'Display The Page Parse Time', configuration_description = 'Display the page parse time on the bottom of each page<br />(Note: This DISPLAYS them. You do NOT need to LOG them to merely display them on your site.)' WHERE configuration_key = 'DISPLAY_PAGE_PARSE_TIME';
UPDATE configuration set configuration_title = 'Log Database Queries', configuration_description = 'Record the database queries to files in the system /logs/ folder. USE WITH CAUTION. This can seriously degrade your site performance and blow out your disk space storage quotas.' WHERE configuration_key = 'STORE_DB_TRANSACTIONS';

UPDATE configuration set configuration_title = 'Enable HTML Emails?', configuration_description = 'Send emails in HTML format if recipient has enabled it in their preferences.' WHERE configuration_key = 'EMAIL_USE_HTML';
UPDATE configuration set configuration_title = 'Email Admin Format?', configuration_description = 'Please select the Admin extra email format (Note: Enable HTML Emails must be on for HTML option to work)' WHERE configuration_key = 'ADMIN_EXTRA_EMAIL_FORMAT';

INSERT INTO address_format VALUES (7, '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country','$city $state / $country');
UPDATE countries set address_format_id = 7 where countries_iso_code_3 = 'AUS';

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



#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.1->1.5.2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.1->1.5.2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT

