#
# * This SQL script upgrades the core Zen Cart database structure from v1.3.0 to v1.3.0.1
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2006 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: mysql_upgrade_zencart_130_to_1301.sql 4243 2006-08-24 10:55:28Z drbyte $
#

## CONFIGURATION TABLE
#insert for those who did an upgrade from 1.2.x to 1.3.0 and thus didn't get the insert:
insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) values ('Define Site Map Status', 'DEFINE_SITE_MAP_STATUS', '1', 'Enable the Defined Site Map Link/Text?<br />0= Link ON, Define Text OFF<br />1= Link ON, Define Text ON<br />2= Link OFF, Define Text ON<br />3= Link OFF, Define Text OFF', '25', '67', now(), now(), NULL, 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'),');
REPLACE INTO configuration_group VALUES ('30', 'EZ-Pages Settings', 'EZ-Pages Settings', 30, '1');

UPDATE configuration SET set_function = 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ' WHERE configuration_key = 'SHOW_TOTALS_IN_CART';



# move to right group (Layout, not prod-info)
update configuration set configuration_group_id=19 where configuration_key = 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP';

#rename descriptions to be more logical
update configuration set configuration_title = 'Send Notice of Pending Reviews Emails To - Status' where configuration_key='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS';
update configuration set configuration_title = 'Send Notice of Pending Reviews Emails To' where configuration_key='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO';
UPDATE configuration set configuration_title='Send Low Stock Emails' where configuration_key = 'SEND_LOWSTOCK_EMAIL';
UPDATE configuration set configuration_title='Send Low Stock Emails To' where configuration_key = 'SEND_EXTRA_LOW_STOCK_EMAILS_TO';



#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.1', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0->1.3.0.1', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='3.0.1', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.3.0->1.3.0.1', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT

