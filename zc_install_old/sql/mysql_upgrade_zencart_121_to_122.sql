# This SQL script upgrades the core Zen Cart database structure from v1.2.1 to v1.2.2
#
# $Id: mysql_upgrade_zencart_121_to_122.sql 18695 2011-05-04 05:24:19Z drbyte $
#

## CONFIGURATION TABLE
UPDATE configuration set configuration_title='Send Copy of Order Confirmation Emails To', configuration_description ='Send COPIES of order confirmation emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;' WHERE configuration_key = 'SEND_EXTRA_ORDER_EMAILS_TO';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Friendly-Errors', 'EMAIL_FRIENDLY_ERRORS', 'true', 'Do you want to display friendly errors if emails fail?  Setting this to false will display PHP errors and likely cause the script to fail. Only set to false while troubleshooting.', '12', '7', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Shopping Cart - Delete Checkboxes or Delete Button', 'SHOW_SHOPPING_CART_DELETE', '3', 'Show on Shopping Cart Delete Button and/or Checkboxes<br /><br />1= Delete Button Only<br />2= Checkbox Only<br />3= Delete Button and Checkbox Only', '9', '20', 'zen_cfg_select_option(array(\'1\', \'2\', \'3\'), ', now());

## Table Structure updates

#orders table
ALTER TABLE orders ADD COLUMN payment_module_code varchar(32) NOT NULL default '' AFTER payment_method;
ALTER TABLE orders ADD COLUMN shipping_method varchar(32) NOT NULL default '' AFTER payment_module_code;
ALTER TABLE orders ADD COLUMN shipping_module_code varchar(32) NOT NULL default '' AFTER shipping_method;

#paypal 
ALTER TABLE paypal ADD COLUMN zen_order_id int(17) NOT NULL default '0' AFTER paypal_ipn_id;
DROP TABLE IF EXISTS orders_session_info;
DROP TABLE IF EXISTS paypal_payment_status;
DROP TABLE IF EXISTS paypal_payment_status_history;

#DROP TABLE IF EXISTS paypal_session;
CREATE TABLE paypal_session (
  unique_id int(11) NOT NULL auto_increment,
  session_id text NOT NULL,
  saved_session blob NOT NULL,
  expiry int(17) NOT NULL default '0',
  PRIMARY KEY  (unique_id)
) ;



#Version Control
ALTER TABLE project_version CHANGE COLUMN project_version_patch_major project_version_patch1 varchar(20) NOT NULL default '';
ALTER TABLE project_version CHANGE COLUMN project_version_patch_minor project_version_patch2 varchar(20) NOT NULL default '';
ALTER TABLE project_version ADD COLUMN project_version_patch1_source varchar(20) NOT NULL default '' AFTER project_version_patch2;
ALTER TABLE project_version ADD COLUMN project_version_patch2_source varchar(20) NOT NULL default '' AFTER project_version_patch1_source;
ALTER TABLE project_version DROP COLUMN project_version_ip_address;

CREATE TABLE project_version_history (
  project_version_id tinyint(3) NOT NULL auto_increment,
  project_version_key varchar(40) NOT NULL default '',
  project_version_major varchar(20) NOT NULL default '',
  project_version_minor varchar(20) NOT NULL default '',
  project_version_patch varchar(20) NOT NULL default '',
  project_version_comment varchar(250) NOT NULL default '',
  project_version_date_applied datetime NOT NULL default '0001-01-01 01:01:01',
  project_version_ip_address varchar(20) NOT NULL default '',
  PRIMARY KEY  (project_version_id),
  UNIQUE KEY project_version_key (project_version_key)
) COMMENT='Database Version Tracking History';




## THE FOLLOWING SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
#NEXT_X_ROWS_AS_ONE_COMMAND:2
UPDATE project_version SET project_version_major='1', project_version_minor='2.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='2.2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT
