# This SQL script upgrades the core Zen Cart database structure from v1.2.0 to v1.2.1
#
# $Id: mysql_upgrade_zencart_120_to_121.sql 4243 2006-08-24 10:55:28Z drbyte $
#

## CONFIGURATION TABLE
UPDATE configuration set configuration_title = 'Define Conditions of Use' WHERE configuration_key = 'DEFINE_CONDITIONS_STATUS';
UPDATE configuration SET configuration_description = 'Automatically check to see if a new version of Zen-Cart is available. Enabling this can sometimes slow down the loading of Admin pages. (Displayed on main Index page after login, and Server Info page.)' WHERE configuration_key = 'SHOW_VERSION_UPDATE_IN_HEADER';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Prices with Tax in Admin', 'DISPLAY_PRICE_WITH_TAX_ADMIN', 'false', 'Display prices with tax included (true) or add the tax at the end (false) in Admin(Invoices)', '1', '21', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
UPDATE configuration SET configuration_description='Products Display Order by<br />0= Product ID<br />1= Product Name<br />2= Model<br />3= Price, Product Name<br />4= Price, Model<br />5= Product Name, Model<br />6= Product Sort Order', set_function='zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'Product ID\'), array(\'id\'=>\'1\', \'text\'=>\'Name\'), array(\'id\'=>\'2\', \'text\'=>\'Product Model\'), array(\'id\'=>\'3\', \'text\'=>\'Product Price - Name\'), array(\'id\'=>\'4\', \'text\'=>\'Product Price - Model\'), array(\'id\'=>\'5\', \'text\'=>\'Product Name - Model\'), array(\'id\'=>\'6\', \'text\'=>\'Product Sort Order\')),' WHERE configuration_key='PRODUCT_INFO_PREVIOUS_NEXT_SORT';
UPDATE configuration SET configuration_title = 'Enable phpBB linkage?', configuration_description = 'Should Zen Cart synchronize new account information to your (already-installed) phpBB forum?' WHERE configuration_key = 'PHPBB_LINKS_ENABLED';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Customers Referral Status', 'CUSTOMERS_REFERRAL_STATUS', '0', 'Customers Referral Code is created from<br />0= Off<br />1= 1st Discount Coupon Code used<br />2= Customer can add during create account or edit if blank<br /><br />NOTE: Once the Customers Referral Code has been set it can only be changed in the Admin Customer', '5', '80', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Category Counts - Admin', 'SHOW_COUNTS_ADMIN', 'true', 'Show Category Counts in Admin?', '1', '130', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display "Newsletter Unsubscribe" Link?', 'SHOW_NEWSLETTER_UNSUBSCRIBE_LINK', 'true', 'Show "Newsletter Unsubscribe" link in the "Information" side-box?', '12', '70', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());


## Table Structure updates to handle better use of decimal points
ALTER TABLE customers_basket CHANGE COLUMN customers_basket_quantity customers_basket_quantity FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE orders_products CHANGE COLUMN products_quantity products_quantity FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_quantity products_quantity FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_ordered products_ordered FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_quantity_order_min products_quantity_order_min FLOAT DEFAULT '1' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_quantity_order_units products_quantity_order_units FLOAT DEFAULT '1' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_quantity_order_max products_quantity_order_max FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products_discount_quantity CHANGE COLUMN discount_qty discount_qty FLOAT DEFAULT '0' NOT NULL;

## Customers Table additions
ALTER TABLE customers ADD COLUMN customers_referral VARCHAR(32) NOT NULL default '';

## Add Coupon_Code to Order Table:
ALTER TABLE orders ADD COLUMN coupon_code varchar(32) NOT NULL default '' AFTER payment_method;

## LAYOUT BOXES TABLE
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'document_categories.php', 1, 0, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'music_genres.php', 1, 1, 0, 0, 0);
INSERT INTO layout_boxes (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('default_template_settings', 'record_companies.php', 1, 1, 0, 0, 0);
UPDATE layout_boxes SET layout_box_status_single=127 WHERE layout_template = 'default_template_settings' AND layout_box_name = 'banner_box.php';

## Query Builder Update
UPDATE query_builder set query_string ='select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o where c.customers_newsletter = \'1\' AND c.customers_id = o.customers_id and o.date_purchased < subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address order by c.customers_lastname, c.customers_firstname ASC' WHERE query_id='3';
UPDATE query_builder set query_string ='select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o where c.customers_newsletter = \'1\' AND c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address order by c.customers_lastname, c.customers_firstname ASC' WHERE query_id='4';
UPDATE query_builder set query_string ='select c.customers_email_address, c.customers_lastname, c.customers_firstname from TABLE_CUSTOMERS c, TABLE_ORDERS o WHERE c.customers_id = o.customers_id and o.date_purchased > subdate(now(),INTERVAL 3 MONTH) GROUP BY c.customers_email_address order by c.customers_lastname, c.customers_firstname ASC' WHERE query_id='5';

## Reset Sales and Salemaker expiry dates if null or 0000-00-00
UPDATE specials SET expires_date='0001-01-01' where expires_date='0000-00-00';
UPDATE specials SET specials_date_available ='0001-01-01' where specials_date_available ='0000-00-00';


## PayPal IPN Updates
ALTER TABLE orders_products_attributes ADD products_options_id INT( 11 ) DEFAULT '0' NOT NULL;
ALTER TABLE orders_products_attributes ADD products_options_values_id INT( 11 ) DEFAULT '0' NOT NULL;

DROP TABLE IF EXISTS orders_session_info;
CREATE TABLE orders_session_info (
  txn_signature varchar(32) NOT NULL default '',
  orders_id int(11) NOT NULL default '0',
  sendto int(11) NOT NULL default '1',
  billto int(11) NOT NULL default '1',
  language varchar(32) NOT NULL default '',
  currency char(3) NOT NULL default '',
  firstname varchar(32) NOT NULL default '',
  lastname varchar(32) NOT NULL default '',
  content_type varchar(32) NOT NULL default '',
  PRIMARY KEY (txn_signature,orders_id),
  KEY idx_orders_session_info_txn_signature (txn_signature)
);

DROP TABLE IF EXISTS paypal;
CREATE TABLE paypal (
  paypal_ipn_id int(11) unsigned NOT NULL auto_increment,
  txn_type varchar(10) NOT NULL default '',
  reason_code varchar(15) default NULL,
  payment_type varchar(7) NOT NULL default '',
  payment_status varchar(17) NOT NULL default '',
  pending_reason varchar(14) default NULL,
  invoice varchar(64) default NULL,
  mc_currency char(3) NOT NULL default '',
  first_name varchar(32) NOT NULL default '',
  last_name varchar(32) NOT NULL default '',
  payer_business_name varchar(64) default NULL,
  address_name varchar(32) default NULL,
  address_street varchar(64) default NULL,
  address_city varchar(32) default NULL,
  address_state varchar(32) default NULL,
  address_zip varchar(10) default NULL,
  address_country varchar(64) default NULL,
  address_status varchar(11) default NULL,
  payer_email varchar(96) NOT NULL default '',
  payer_id varchar(32) NOT NULL default '',
  payer_status varchar(10) NOT NULL default '',
  payment_date datetime default NULL,
  business varchar(96) NOT NULL default '',
  receiver_email varchar(96) NOT NULL default '',
  receiver_id varchar(32) NOT NULL default '',
  txn_id varchar(17) NOT NULL default '',
  parent_txn_id varchar(17) default NULL,
  num_cart_items tinyint(4) unsigned NOT NULL default '1',
  mc_gross decimal(7,2) NOT NULL default '0.00',
  mc_fee decimal(7,2) NOT NULL default '0.00',
  payment_gross decimal(7,2) default NULL,
  payment_fee decimal(7,2) default NULL,
  settle_amount decimal(7,2) default NULL,
  settle_currency char(3) default NULL,
  exchange_rate decimal(4,2) default NULL,
  notify_version decimal(2,1) NOT NULL default '0.0',
  verify_sign varchar(128) NOT NULL default '',
  last_modified datetime default NULL,
  date_added datetime default NULL,
  memo text,
  PRIMARY KEY (paypal_ipn_id,txn_id),
  KEY idx_paypal_paypal_ipn_id (paypal_ipn_id)
);

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

DROP TABLE IF EXISTS paypal_payment_status_history;
CREATE TABLE paypal_payment_status_history (
  payment_status_history_id int(11) NOT NULL auto_increment,
  paypal_ipn_id int(11) NOT NULL default '0',
  payment_status varchar(17) NOT NULL default '',
  pending_reason varchar(14) default NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY (payment_status_history_id)
);

## These tables are obsolete after the new PayPal IPN is installed
DROP TABLE IF EXISTS paypal_ipn_address_status;
DROP TABLE IF EXISTS paypal_ipn_mc_currency;
DROP TABLE IF EXISTS paypal_ipn_payment_status;
DROP TABLE IF EXISTS paypal_ipn_payment_type;
DROP TABLE IF EXISTS paypal_ipn_pending_reason;
DROP TABLE IF EXISTS paypal_ipn_reason_code;
DROP TABLE IF EXISTS paypal_ipn_txn_type;

## Migrate Paypal IPN data from 1.2.0 format to 1.2.1 format:
#NEXT_X_ROWS_AS_ONE_COMMAND:1
INSERT INTO paypal 
(paypal_ipn_id, txn_type, reason_code, payment_type, payment_status, pending_reason, 
invoice, mc_currency, first_name, last_name, payer_business_name, address_name, 
address_street, address_city, address_state, address_zip, address_country, address_status, 
payer_email, payer_id, payer_status, 
payment_date, business, receiver_email, receiver_id, txn_id, 
num_cart_items, mc_gross, mc_fee, payment_gross, payment_fee, settle_amount, 
exchange_rate, notify_version, verify_sign, date_added, memo )
SELECT p.paypal_ipn_id, p.txn_type, p.reason_code, p.payment_type, p.payment_status, 
p.pending_reason, p.invoice, p.mc_currency, 
p.first_name, p.last_name, p.payer_business_name, p.address_name, p.address_street, 
p.address_city, p.address_state, p.address_zip, p.address_country, p.address_status, 
p.payer_email, p.payer_id, p.payer_status, 
p.payment_date, p.business, p.receiver_email, p.receiver_id, p.txn_id, 
po.num_cart_items, po.mc_gross, po.mc_fee, po.payment_gross, po.payment_fee, po.settle_amount, 
po.exchange_rate, p.notify_version, p.verify_sign, p.date_added, pm.memo
FROM (paypal_ipn p, paypal_ipn_orders_memo pm
LEFT JOIN paypal_ipn_orders po
ON p.paypal_ipn_id = po.paypal_ipn_id)
WHERE p.paypal_ipn_id = pm.paypal_ipn_id;

UPDATE paypal SET payment_status='Completed' where payment_status=1;
UPDATE paypal SET payment_status='Pending' where payment_status=2;
UPDATE paypal SET payment_status='Failed' where payment_status=3;
UPDATE paypal SET payment_status='Denied' where payment_status=4;
UPDATE paypal SET payment_status='Refunded' where payment_status=5;
UPDATE paypal SET payment_status='Cancelled' where payment_status=6;
UPDATE paypal SET payment_type='instant' where payment_type=1;
UPDATE paypal SET payment_type='echeck' where payment_type=2;
UPDATE paypal SET pending_reason='' where pending_reason=0;
UPDATE paypal SET pending_reason='echeck' where pending_reason=1;
UPDATE paypal SET pending_reason='multi-currency' where pending_reason=2;
UPDATE paypal SET pending_reason='intl' where pending_reason=3;
UPDATE paypal SET pending_reason='Verify' where pending_reason=4;
UPDATE paypal SET pending_reason='address' where pending_reason=5;
UPDATE paypal SET pending_reason='upgrade' where pending_reason=6;
UPDATE paypal SET pending_reason='unilateral' where pending_reason=7;
UPDATE paypal SET pending_reason='other' where pending_reason=8;
UPDATE paypal SET reason_code='' where reason_code=0;
UPDATE paypal SET reason_code='chargeback' where reason_code=1;
UPDATE paypal SET reason_code='guarantee' where reason_code=2;
UPDATE paypal SET reason_code='buyer_complaint' where reason_code=3;
UPDATE paypal SET reason_code='other' where reason_code=4;
UPDATE paypal SET txn_type='web_accept' where txn_type=1;
UPDATE paypal SET txn_type='cart' where txn_type=2;
UPDATE paypal SET txn_type='send_money' where txn_type=3;
UPDATE paypal SET txn_type='reversal' where txn_type=4;
UPDATE paypal SET mc_currency='USD' where mc_currency=1;
UPDATE paypal SET mc_currency='GBP' where mc_currency=2;
UPDATE paypal SET mc_currency='EUR' where mc_currency=3;
UPDATE paypal SET mc_currency='CAD' where mc_currency=4;
UPDATE paypal SET mc_currency='JPY' where mc_currency=5;
UPDATE paypal SET address_status='confirmed' where address_status=1;
UPDATE paypal SET address_status='unconfirmed' where address_status=2;

#NEXT_X_ROWS_AS_ONE_COMMAND:1
INSERT INTO paypal_payment_status_history 
(paypal_ipn_id, payment_status, pending_reason, date_added)
SELECT paypal_ipn_id, payment_status, pending_reason, date_added
FROM paypal;

DROP TABLE IF EXISTS paypal_ipn;
DROP TABLE IF EXISTS paypal_ipn_orders;
DROP TABLE IF EXISTS paypal_ipn_orders_memo;

## END PAYPAL_IPN_MIGRATION



## THE FOLLOWING SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
UPDATE project_version SET project_version_major='1', project_version_minor='2.1', project_version_patch_major='', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='2.1', project_version_patch_major='', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

## one final typo:
## the following fixes misspelled column name from 1.2.0:
ALTER TABLE product_types CHANGE COLUMN date_addded date_added datetime NOT NULL default '0001-01-01 00:00:00';

#####  END OF UPGRADE SCRIPT
