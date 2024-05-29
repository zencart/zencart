#
# * This SQL script upgrades the core Zen Cart database structure from v2.0.0 to v2.1.0
# *
# * @access private
# * @copyright Copyright 2003-2024 Zen Cart Development Team
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id:   $
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

#PROGRESS_FEEDBACK:!TEXT=Purging caches ...
# Clear out active customer sessions. Truncating helps the database clean up behind itself.
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;

#PROGRESS_FEEDBACK:!TEXT=Adding error logging to email archive ...
# Add column to store any errorinfo returned from phpmailer.
ALTER TABLE email_archive ADD COLUMN errorinfo TEXT DEFAULT NULL;
ALTER TABLE email_archive ADD INDEX idx_email_date_sent_zen (date_sent);

#PROGRESS_FEEDBACK:!TEXT=Updating table structures!
ALTER TABLE admin ADD COLUMN mfa TEXT DEFAULT NULL;
DROP TABLE IF EXISTS admin_expired_tokens;
CREATE TABLE admin_expired_tokens (
  admin_name varchar(44) NOT NULL default '',
  otp_code varchar(32) NOT NULL default '',
  used_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (used_date, otp_code, admin_name),
  KEY idx_admin_name_otp_code_zen (admin_name, otp_code)
);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('MFA Multi-Factor Authentication Required', 'MFA_ENABLED', 'False', '2-Factor authentication for Admin users', 1, 29, now(), 'zen_cfg_select_option([\'True\', \'False\'],');




# Image matching mode
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Additional Images matching pattern', 'ADDITIONAL_IMAGES_MODE', 'legacy', '&quot;strict&quot; = always use &quot;_&quot; suffix<br>&quot;legacy&quot; = only use &quot;_&quot; suffix in subdirectories<br>(Before v210 legacy was the default)<br>Default = strict', '4', '25', 'zen_cfg_select_option(array(\'strict\', \'legacy\'), ', now());



#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='2', project_version_minor='1.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.0.0->2.1.0-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='2', project_version_minor='1.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.0.0->2.1.0-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
