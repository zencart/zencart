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
#DELETE FROM customer_password_reset_tokens WHERE created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1440 MINUTE);


#PROGRESS_FEEDBACK:!TEXT=Updating table structures!
DROP TABLE IF EXISTS customer_password_reset_tokens;
CREATE TABLE customer_password_reset_tokens (
    customer_id int(11) NOT NULL default 0,
    token varchar(100) NOT NULL default '',
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (token, customer_id)
);
ALTER TABLE orders_products_attributes MODIFY products_options varchar(191) NOT NULL default '';
ALTER TABLE products_options MODIFY products_options_name varchar(191) NOT NULL default '';
ALTER TABLE products_options_values MODIFY products_options_values_name varchar(191) NOT NULL default '';
ALTER TABLE currencies MODIFY code char(4) NOT NULL default '';
ALTER TABLE orders MODIFY currency char(4) default NULL;
ALTER TABLE plugin_control MODIFY `version` varchar(20);
ALTER TABLE plugin_control_versions MODIFY `version` varchar(20);

#PROGRESS_FEEDBACK:!TEXT=Updating configuration settings...
DELETE FROM configuration WHERE configuration_key IN ('REPORT_ALL_ERRORS_ADMIN', 'REPORT_ALL_ERRORS_STORE', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, val_function) VALUES ('Password Reset Token Length', 'PASSWORD_RESET_TOKEN_LENGTH', '24', 'Number of characters in a generated password-reset token. Default is 24. Allowed: 12-100, but it affects the URL length, so 12-30 is most ideal', 1, 32, NULL, now(), '{\"error\":\"TEXT_HINT_PASSWORD_RESET_TOKEN_LENGTH\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":12, \"max_range\":100}}}');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, val_function) VALUES ('Password Reset Token Valid For', 'PASSWORD_RESET_TOKEN_MINUTES_VALID', '60', 'How many minutes a password-reset token is valid for. Default: 60 minutes (1 hour). Allowed: 1-1440. Best is 60-120 minutes.', 1, 32, NULL, now(), '{\"error\":\"TEXT_HINT_PASSWORD_RESET_TOKEN_VALID_MINUTES\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":1, \"max_range\":1440}}}');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('TinyMCE Editor API Key', 'TINYMCE_EDITOR_API_KEY', 'GPL', 'Basic editor features are free, in GPL mode.<br>Optionally enable premium editor features in the TinyMCE editor by providing your account API key and register your store website domain in your Tiny account.<br>Sign up at <a href="https://www.tiny.cloud/auth/signup/" target="_blank">www.tiny.cloud</a><br><br>Default value: <strong>GPL</strong> for free-unregistered mode with basic features.', 1, 111, now());
UPDATE configuration SET configuration_description = 'CSS Buttons<br>Use CSS buttons instead of images (GIF/JPG)?<br>Button styles must be configured in the stylesheet if you enable this option.<br>Yes - Use CSS buttons<br>No - Use images buttons<br>Found - Use images if exist, else use CSS buttons', set_function = 'zen_cfg_select_option(array(\'No\', \'Yes\', \'Found\'), ' WHERE configuration_key = 'IMAGE_USE_CSS_BUTTONS';


#PROGRESS_FEEDBACK:!TEXT=Creating new table tax_rates_description...
# Table structure for table 'tax_rates_description'
CREATE TABLE IF NOT EXISTS tax_rates_description (
  id int(11) NOT NULL auto_increment,
  tax_rates_id int(11) NOT NULL default 0,
  language_id int(11) NOT NULL default 1,
  tax_description varchar(250) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_rate_lang_zen (tax_rates_id,language_id)
) ENGINE=MyISAM;
# Transfer any existing tax_description entries from tax_rates to tax_rates_description, then drop the tax_description column from tax_rates.
# This is done via dynamic SQL to avoid errors if the tax_description column does not exist (for example, if this upgrade script is run on a database that has already been partially upgraded).
# Note that the formatting here is intentionally without indentation and with spaces and quotes in strange places, because we do tablename parsing to insert any table prefixes.
#NEXT_X_ROWS_AS_ONE_COMMAND:9
SELECT EXISTS(
SELECT 1
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tax_rates'
AND COLUMN_NAME = 'tax_description'
) INTO @has_col;
SET @copyrecords = CASE
WHEN @has_col = 1 THEN '
INSERT INTO tax_rates_description (tax_rates_id, language_id, tax_description)
SELECT tr.tax_rates_id, lg.languages_id, tr.tax_description
FROM tax_rates tr
CROSS JOIN languages lg;'
ELSE
'SELECT 1;'
END;
SET @dropcolumn = CASE
WHEN @has_col = 1 THEN '
ALTER TABLE tax_rates DROP COLUMN tax_description ;'
ELSE
'SELECT 1;'
END;
PREPARE stmt FROM @copyrecords;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
PREPARE stmt2 FROM @dropcolumn;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

#PROGRESS_FEEDBACK:!TEXT=Creating new table products_additional_images...
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Additional Images Approach', 'ADDITIONAL_IMAGES_APPROACH', 'legacy', 'Use Legacy mode for old-school directory scanning and matching filenames. Additional images must be uploaded manually via FTP or using Image Handler.<br>Use Modern mode to add additional images directly from the admin product page. Filenames and extensions do not need to match.<br><strong>NOTE:</strong> if you are switching from Legacy to Modern for the first time, you can use the converter tool found in your Tools menu to update all existing product data with existing additional images. Switching back and forth is not recommended.', '4', '80', 'zen_cfg_select_option([\'legacy\', \'modern\'], ', NOW());
CREATE TABLE IF NOT EXISTS products_additional_images (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  products_id INT(11) NOT NULL,
  additional_image VARCHAR(255) NOT NULL,
  sort_order INT(11) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY idx_pid_img_zen (products_id, additional_image)
) ENGINE=MyISAM


#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='2', project_version_minor='2.0-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.1.0->2.2.0-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='2', project_version_minor='2.0-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.1.0->2.2.0-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
