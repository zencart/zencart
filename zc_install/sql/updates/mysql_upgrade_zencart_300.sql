#
# * This SQL script upgrades the core Zen Cart database structure from v2.2.0 to v3.0.0
# *
# * @access private
# * @copyright Copyright 2003-2026 Zen Cart Development Team
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id:  New in v3.0.0 $
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
DELETE FROM customer_password_reset_tokens WHERE created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1440 MINUTE);
DELETE FROM customers_auth_tokens WHERE created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1440 MINUTE);


#PROGRESS_FEEDBACK:!TEXT=Updating table structures!
DROP TABLE IF EXISTS paypal_testing;
ALTER TABLE admin ADD dashboard_layout TEXT NULL;
ALTER TABLE reviews_description ADD reviews_title VARCHAR(128) NOT NULL DEFAULT '';
ALTER TABLE products_description DROP COLUMN products_viewed;


#PROGRESS_FEEDBACK:!TEXT=Updating configuration settings...

# update to new default, only if not customized from the original default of 50.
UPDATE configuration SET configuration_value = '5' WHERE configuration_key = 'REVIEW_TEXT_MIN_LENGTH' AND configuration_value = 50 AND (last_modified IS NULL OR last_modified = date_added);







#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
SET @VERSION_MAJOR = '3';
SET @VERSION_MINOR = '0.0-dev';
SET @DB_MAJOR = '2';
SET @DB_MINOR = '9.9';

UPDATE project_version
SET
    project_version_major = @VERSION_MAJOR,
    project_version_minor = @VERSION_MINOR,
    project_version_patch1 = '',
    project_version_patch1_source = '',
    project_version_patch2 = '',
    project_version_patch2_source = '',
    project_version_comment = CONCAT('Version Update to ', @VERSION_MAJOR, '.', @VERSION_MINOR),
    project_version_date_applied = now()
WHERE project_version_key = 'Zen-Cart Main';

UPDATE project_version
SET
    project_version_major = @DB_MAJOR,
    project_version_minor = @DB_MINOR,
    project_version_patch1 = '',
    project_version_patch1_source = '',
    project_version_patch2 = '',
    project_version_patch2_source = '',
    project_version_comment = CONCAT('Version Update to ', @DB_MAJOR, '.', @DB_MINOR),
    project_version_date_applied = now()
WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
