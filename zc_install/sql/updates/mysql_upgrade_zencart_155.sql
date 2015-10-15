#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.4 to v1.5.5
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2015 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version GIT: $Id: Author: DrByte  Thu Sept 10 2015  New in v1.5.5 $
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

UPDATE countries set countries_name = 'Åland Islands' where countries_iso_code_3 = 'ALA';
UPDATE countries set countries_name = 'Réunion' where countries_iso_code_3 = 'REU';
UPDATE countries set countries_name = "Côte d'Ivoire" where countries_iso_code_3 = 'CIV';
UPDATE countries set countries_name = 'Bonaire, Sint Eustatius and Saba', countries_iso_code_2 = 'BQ', countries_iso_code_3 = 'BES' WHERE countries_iso_code_3 = 'ANT';
INSERT INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (247,'Curaçao','CW','CUW','1');
INSERT INTO countries (countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) VALUES (248,'Sint Maarten (Dutch part)','SX','SXM','1');

UPDATE configuration SET configuration_title='Credit Card Enable Status - Debit', configuration_key = 'CC_ENABLED_DEBIT', configuration_value ='0', configuration_description='Accept Debit Cards 0= off 1= on<br>NOTE: This is not deeply integrated at this time, and this setting may be redundant if your payment modules do not yet specifically have code to honour this switch.', date_added=now() WHERE configuration_key='CC_ENABLED_SWITCH';

UPDATE configuration set configuration_title = 'Enable HTML Emails?', configuration_description = 'Send emails in HTML format if recipient has enabled it in their preferences.' WHERE configuration_key = 'EMAIL_USE_HTML';
UPDATE configuration set configuration_title = 'Email Admin Format?', configuration_description = 'Please select the Admin extra email format (Note: Enable HTML Emails must be on for HTML option to work)' WHERE configuration_key = 'ADMIN_EXTRA_EMAIL_FORMAT';

UPDATE configuration set sort_order = '1', configuration_description = 'Send out e-mails?<br>(Default state is ON.<br>Turn off to suppress ALL outgoing email messages from this store.)' WHERE configuration_key = 'SEND_EMAILS';
UPDATE configuration set sort_order = '2', configuration_description = 'Defines the method for sending mail.<br /><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br />Servers running on Windows and MacOS should change this setting to <strong>SMTP</strong>.<br /><br /><strong>SMTPAUTH</strong> should be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br /><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br /><strong>"sendmail-f"</strong> is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br /><br /><strong>Qmail</strong> is mostly obsolete and only used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.' WHERE configuration_key = 'EMAIL_TRANSPORT';
UPDATE configuration set configuration_description = 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.<br><br>Default: 25<br>Typical values are:<br>25 - normal unencrypted SMTP<br>587 - encrypted SMTP<br>465 - older MS SMTP port' WHERE configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT';

ALTER TABLE paypal_payment_status_history MODIFY pending_reason varchar(32) default NULL;
ALTER TABLE coupons_description MODIFY coupon_name VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE orders MODIFY shipping_method VARCHAR(255) NOT NULL DEFAULT '';
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
UPDATE project_version SET project_version_major='1', project_version_minor='5.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.5.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.4->1.5.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT

