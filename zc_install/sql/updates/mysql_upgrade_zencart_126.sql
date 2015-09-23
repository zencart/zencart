# This SQL script upgrades the core Zen Cart database structure from v1.2.5 to v1.2.6
#
# $Id: mysql_upgrade_zencart_125_to_126.sql 4243 2006-08-24 10:55:28Z drbyte $
#

DELETE FROM admin where admin_name = 'demo' and admin_pass = '23ce1aad0e04a3d2334c7aef2f8ade83:58';

ALTER TABLE admin_activity_log CHANGE COLUMN log_id log_id int(15) NOT NULL auto_increment;
ALTER TABLE whos_online CHANGE COLUMN user_agent user_agent varchar(255) NOT NULL default '';

#added for future orders editing
ALTER TABLE orders_products ADD products_prid TINYTEXT NOT NULL;
ALTER TABLE orders_products_attributes ADD products_prid TINYTEXT NOT NULL;
ALTER TABLE orders_products_download ADD products_prid TINYTEXT NOT NULL;



## CONFIGURATION TABLE
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories-Tabs Menu ON/OFF', 'CATEGORIES_TABS_STATUS', '0', 'Categories-Tabs<br />This enables the display of your store\'s categories as a menu across the top of your header. There are many potential creative uses for this.<br />0= Hide Categories Tabs<br />1= Show Categories Tabs', '19', '112', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());


#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='2.6', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.5->1.2.6', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='2.6', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.5->1.2.6', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT