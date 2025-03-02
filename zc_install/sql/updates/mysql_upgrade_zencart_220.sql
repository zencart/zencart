#
# * This SQL script upgrades the core Zen Cart database structure from v2.1.0 to v2.2.0
# *
# * @access private
# * @copyright Copyright 2003-2025 Zen Cart Development Team
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: New in v2.2.0 $
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

#PROGRESS_FEEDBACK:!TEXT=Updating table structures!
DROP TABLE IF EXISTS customer_password_reset_tokens;
CREATE TABLE customer_password_reset_tokens (
    customer_id int(11) NOT NULL default 0,
    token varchar(100) NOT NULL default '',
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (token, customer_id)
);


#PROGRESS_FEEDBACK:!TEXT=Updating configuration settings...
DELETE FROM configuration WHERE configuration_key IN ('REPORT_ALL_ERRORS_ADMIN', 'REPORT_ALL_ERRORS_STORE', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, val_function) VALUES ('Password Reset Token Length', 'PASSWORD_RESET_TOKEN_LENGTH', '24', 'Number of characters in a generated password-reset token. Default is 24. Allowed: 12-100, but it affects the URL length, so 12-30 is most ideal', 1, 32, NULL, now(), '{\"error\":\"TEXT_HINT_PASSWORD_RESET_TOKEN_LENGTH\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":10, \"max_range\":100}}}');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, val_function) VALUES ('Password Reset Token Valid For', 'PASSWORD_RESET_TOKEN_MINUTES_VALID', '60', 'How many minutes a password-reset token is valid for. Default: 60 minutes (1 hour). Allowed: 1-1440. Best is 60-120 minutes.', 1, 32, NULL, now(), '{\"error\":\"TEXT_HINT_PASSWORD_RESET_TOKEN_VALID_MINUTES\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":1, \"max_range\":1440}}}');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('TinyMCE Editor API Key', 'TINYMCE_EDITOR_API_KEY', 'GPL', 'Basic editor features are free, in GPL mode.<br>Optionally enable premium editor features in the TinyMCE editor by providing your account API key and register your store website domain in your Tiny account.<br>Sign up at <a href="https://www.tiny.cloud/auth/signup/" target="_blank">www.tiny.cloud</a><br><br>Default value: <strong>GPL</strong> for free-unregistered mode with basic features.', 1, 111, now());


#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='2', project_version_minor='2.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.1.0->2.2.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='2', project_version_minor='2.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.1.0->2.2.0', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
