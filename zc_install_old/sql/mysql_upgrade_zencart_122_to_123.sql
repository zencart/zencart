# This SQL script upgrades the core Zen Cart database structure from v1.2.2 to v1.2.3
#
# $Id: mysql_upgrade_zencart_122_to_123.sql 4243 2006-08-24 10:55:28Z drbyte $
#

## CONFIGURATION TABLE
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories - Always Open to Show SubCategories', 'SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS', '1', 'Always Show Categories and SubCategories<br />0= off, just show Top Categories<br />1= on, Always show Categories and SubCategories when selected', 19, 47, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Default Order Status For Zero Balance Orders', 'DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID', '2', 'When an order\'s balance is zero, this order status will be assigned to it.', '16', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customer Default Email Preference', 'ACCOUNT_EMAIL_PREFERENCE', '0', 'Set the Default Customer Default Email Preference<br />0= Text<br />1= HTML<br /><strong>Note: Defaulting this to accepted may be in violation of certain regulations for your state or country</strong>', 5, 46, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Sending Domain', 'EMAIL_SMTPAUTH_SEND_DOMAIN', 'YourDomain.com', 'Enter the domain name of your shop. In most cases this is the same as your store URL without the HTTP://<br />Only required if using SMTP Authentication for email.', '12', '101', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Account Mailbox', 'EMAIL_SMTPAUTH_MAILBOX', 'YourDomain.com', 'Enter the mailbox account name (me@mydomain.com) supplied by your host. This is the account name that your host requires for SMTP authentication.<br />Only required if using SMTP Authentication for email.', '12', '101', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Account Password', 'EMAIL_SMTPAUTH_PASSWORD', 'none_given', 'Enter the password for your SMTP mailbox. <br />Only required if using SMTP Authentication for email.', '12', '101', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Mail Host', 'EMAIL_SMTPAUTH_MAIL_SERVER', 'YourDomain.com', 'Enter the DNS name of your SMTP mail server.<br />ie: mail.mydomain.com<br />or 55.66.77.88<br />Only required if using SMTP Authentication for email.', '12', '101', now());
UPDATE configuration set configuration_title='Downloads Controller Order Status Value >= lower value', configuration_description ='Downloads Controller Order Status Value - Default >= 2<br /><br />Downloads are available for checkout based on the orders status. Orders with orders status greater than this value will be available for download. The orders status is set for an order by the Payment Modules. Set the lower range for this range.' WHERE configuration_key = 'DOWNLOADS_CONTROLLER_ORDERS_STATUS';
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'Downloads Controller Order Status Value <= upper value', 'DOWNLOADS_CONTROLLER_ORDERS_STATUS_END', '4', 'Downloads Controller Order Status Value - Default <= 4<br /><br />Downloads are available for checkout based on the orders status. Orders with orders status less than this value will be available for download. The orders status is set for an order by the Payment Modules.  Set the upper range for this range.', '13', '13', now(), '', NULL , NULL);

#The following is converted to an UPDATE since the entry existed in prior versions.
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('E-Mail Transport Method', 'EMAIL_TRANSPORT', 'sendmail', 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running on Windows and MacOS should change this setting to SMTP.<br /><br />SMTPAUTH should only be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br />"Sendmail -f" is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.', '12', '1', 'zen_cfg_select_option(array(\'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\'),', now());
UPDATE configuration SET configuration_description = 'Defines if this server uses a local connection to sendmail or uses an SMTP connection via TCP/IP. Servers running on Windows and MacOS should change this setting to SMTP.<br /><br />SMTPAUTH should only be used if your server requires SMTP authorization to send messages. You must also configure your SMTPAUTH settings in the appropriate fields in this admin section.<br /><br />"Sendmail -f" is only for servers which require the use of the -f parameter to send mail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.', set_function = 'zen_cfg_select_option(array(\'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\'),' WHERE configuration_key = 'EMAIL_TRANSPORT';

# Ignore the following 2 lines during an upgrade, since the shop-owner may have a different preference.
# Also, it should be an "UPDATE" and not an "INSERT" statement, since it already exists in prior versions.
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'true', 'Include Tax in calculation.', 6, 6, NULL, '2003-10-30 22:16:36', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');
#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', 'Include Tax in calculation.', 6, 6, NULL, '2003-10-30 22:16:40', NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');


## Table Structure updates
ALTER TABLE paypal CHANGE COLUMN payment_date payment_date datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE paypal CHANGE COLUMN last_modified last_modified datetime NOT NULL default '0001-01-01 00:00:00';
ALTER TABLE paypal CHANGE COLUMN date_added date_added datetime NOT NULL default '0001-01-01 00:00:00';

DROP TABLE IF EXISTS paypal_payment_status;
CREATE TABLE paypal_payment_status (
  payment_status_id int(11) NOT NULL auto_increment,
  payment_status_name varchar(64) NOT NULL default '',
  PRIMARY KEY (payment_status_id)
);
 
INSERT INTO paypal_payment_status VALUES (1, 'Completed');
INSERT INTO paypal_payment_status VALUES (2, 'Pending');
INSERT INTO paypal_payment_status VALUES (3, 'Failed');
INSERT INTO paypal_payment_status VALUES (4, 'Denied');
INSERT INTO paypal_payment_status VALUES (5, 'Refunded');
INSERT INTO paypal_payment_status VALUES (6, 'Canceled_Reversal');
INSERT INTO paypal_payment_status VALUES (7, 'Reversed');


#DROP TABLE IF EXISTS paypal_payment_status_history;
CREATE TABLE paypal_payment_status_history (
  payment_status_history_id int(11) NOT NULL auto_increment,
  paypal_ipn_id int(11) NOT NULL default '0',
  txn_id varchar(64) NOT NULL default '',
  parent_txn_id varchar(64) NOT NULL default '',
  payment_status varchar(17) NOT NULL default '',
  pending_reason varchar(14) default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY (payment_status_history_id)
);

ALTER TABLE project_version_history DROP INDEX project_version_key;



## THE FOLLOWING SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version history to track the fact that the upgrade was installed.
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

UPDATE project_version SET project_version_major='1', project_version_minor='2.3', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.2->1.2.3', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='2.3', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.2->1.2.3', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';



#####  END OF UPGRADE SCRIPT
