#
# * This SQL script upgrades the core Zen Cart database structure from v2.0.0 to v2.1.0
# *
# * @access private
# * @copyright Copyright 2003-2024 Zen Cart Development Team
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: lat9 2024 Jul 25 Modified in v2.1.0-alpha1 $
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
# Postcode/zip fields expand to accomodate Portugal formatting
ALTER TABLE address_book MODIFY entry_postcode varchar(64) NOT NULL default '';
ALTER TABLE orders MODIFY customers_postcode varchar(64) NOT NULL default '';
ALTER TABLE orders MODIFY delivery_postcode varchar(64) NOT NULL default '';
ALTER TABLE orders MODIFY billing_postcode varchar(64) NOT NULL default '';
ALTER TABLE paypal MODIFY address_zip varchar(64) default NULL;
ALTER TABLE paypal_testing MODIFY address_zip varchar(64) default NULL;

ALTER TABLE admin ADD COLUMN mfa TEXT DEFAULT NULL;
DROP TABLE IF EXISTS admin_expired_tokens;
CREATE TABLE admin_expired_tokens (
  admin_name varchar(44) NOT NULL default '',
  otp_code varchar(32) NOT NULL default '',
  used_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (used_date, otp_code, admin_name),
  KEY idx_admin_name_otp_code_zen (admin_name, otp_code)
);

DROP TABLE IF EXISTS featured_categories;
CREATE TABLE featured_categories (
  featured_categories_id int(11) NOT NULL auto_increment,
  categories_id int(11) NOT NULL default 0,
  featured_date_added datetime default NULL,
  featured_last_modified datetime default NULL,
  expires_date date NOT NULL default '0001-01-01',
  date_status_change datetime default NULL,
  status int(1) NOT NULL default 1,
  featured_date_available date NOT NULL default '0001-01-01',
  PRIMARY KEY  (featured_categories_id),
  KEY idx_status_zen (status),
  KEY idx_category_id_zen (categories_id),
  KEY idx_date_avail_zen (featured_date_available),
  KEY idx_expires_date_zen (expires_date)
);


INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('MFA Multi-Factor Authentication Required', 'MFA_ENABLED', 'False', '2-Factor authentication for Admin users', 1, 29, now(), 'zen_cfg_select_option([\'True\', \'False\'],');


#Featured Category Settings
INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('featured_categories', 'BOX_CATALOG_FEATURED_CATEGORIES', 'FILENAME_FEATURED_CATEGORIES', '', 'catalog', 'Y', 13);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function, val_function) VALUES ('Show Featured Categories on Main Page', 'SHOW_PRODUCT_INFO_MAIN_FEATURED_CATEGORIES', '5', 'Show Featured Categories on Main Page<br>0= off or set the sort order', 24, 68, NULL, now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ', NULL);
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function, val_function) VALUES ('Random Featured Categories for SideBox', 'MAX_RANDOM_SELECT_FEATURED_CATEGORIES', '1', 'Number of random FEATURED categories to rotate in the sidebox<br>Enter the number of categories to display in this sidebox at one time.<br><br>How many categories do you want to display in this sidebox?', 3, 32, NULL, now(), NULL, NULL, '{\"error\":\"TEXT_MAX_ADMIN_RANDOM_SELECT_FEATURED_CATEGORIES_LENGTH\",\"id\":\"FILTER_VALIDATE_INT\",\"options\":{\"options\":{\"min_range\":0}}}');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function, val_function) VALUES ('Categories Box - Show Featured Category Link', 'SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES', 'true', 'Show Featured Categories Link in the Categories Box', 19, 11, NULL, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),',NULL);

# Image matching mode
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Additional Images matching pattern', 'ADDITIONAL_IMAGES_MODE', 'legacy', '&quot;strict&quot; = always use &quot;_&quot; suffix<br>&quot;legacy&quot; = only use &quot;_&quot; suffix in subdirectories<br>(Before v210 legacy was the default)<br>Default = strict', '4', '25', 'zen_cfg_select_option(array(\'strict\', \'legacy\'), ', now());

# Updates
UPDATE configuration SET configuration_description = 'Product Listing Default sort order?<br>NOTE: Leave Blank for Product Sort Order; otherwise use a number from 1-8 corresponding to the sort order dropdown on the listing page. Example: 1' WHERE configuration_key = 'PRODUCT_LISTING_DEFAULT_SORT_ORDER';

#There are now five featured main page items so add one more
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE configuration_key = 'SHOW_PRODUCT_INFO_MAIN_NEW_PRODUCTS';
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE configuration_key = 'SHOW_PRODUCT_INFO_MAIN_FEATURED_PRODUCTS';
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE configuration_key = 'SHOW_PRODUCT_INFO_MAIN_SPECIALS_PRODUCTS';
UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'), ' WHERE configuration_key = 'SHOW_PRODUCT_INFO_MAIN_UPCOMING';

#Change some text to include Category for overlapping entries
UPDATE configuration SET configuration_title = 'Featured Products And Categories - Number to Display Admin' WHERE configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN';
UPDATE configuration SET configuration_title = 'Featured Products And Categories Centerbox' WHERE configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED';
UPDATE configuration SET configuration_title = 'Image - Featured Products And Categories Width' WHERE configuration_key = 'IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH';
UPDATE configuration SET configuration_title = 'Image - Featured Products And Categories Height' WHERE configuration_key = 'IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT';
UPDATE configuration SET configuration_title = 'Product And Category Image - No Image Status' WHERE configuration_key = 'PRODUCTS_IMAGE_NO_IMAGE_STATUS';
UPDATE configuration SET configuration_title = 'Product And Category Image - No Image picture' WHERE configuration_key = 'PRODUCTS_IMAGE_NO_IMAGE';
UPDATE configuration SET configuration_title = 'Featured Products And Categories Columns per Row' WHERE configuration_key = 'SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS';


#PROGRESS_FEEDBACK:!TEXT=Updating ez-pages ...
ALTER TABLE ezpages ADD status_mobile TINYINT(1) NOT NULL DEFAULT 1 AFTER alt_url_external;
ALTER TABLE ezpages ADD mobile_sort_order TINYINT(1) NOT NULL DEFAULT 0 AFTER status_toc;
ALTER TABLE ezpages ADD INDEX idx_ezp_status_mobile_zen (status_mobile);
ALTER TABLE ezpages MODIFY page_is_ssl INT(1) NOT NULL default 1;

#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='2', project_version_minor='1.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.0.0->2.1.0-alpha2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='2', project_version_minor='1.0', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 2.0.0->2.1.0-alpha2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
