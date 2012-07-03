# The following commands are used to upgrade the Zen Cart v1.1.0 database structure to v1.1.1 format.
#
# $Id: mysql_upgrade_zencart_110_to_111.sql 4243 2006-08-24 10:55:28Z drbyte $
#


DELETE FROM configuration WHERE configuration_key = 'PRODUCTS_OPTIONS_SORT_ORDER';
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id,
sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'Products Info - Products Option Sort Order', 'PRODUCTS_OPTIONS_SORT_ORDER', '0',
'Sort order of Option Names for Products Info<br>0= Sort Order, Option Name<br>1= Option Name', 18, 35, now(), now(), NULL, 'zen_cfg_select_option(array(\'0\',
\'1\'),');

DELETE FROM configuration WHERE configuration_key = 'PRODUCTS_OPTIONS_SORT_BY_PRICE';
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id,
sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'Products Info - Attributes Sort Order', 'PRODUCTS_OPTIONS_SORT_BY_PRICE', '1', 'Sort
order of Attributes for Products Info<br>0= Sort Order, Price<br>1= Sort Order, Option Value Name', 18, 36, now(), now(), NULL, 'zen_cfg_select_option(array(\'0\',
\'1\'),');

UPDATE configuration_group SET configuration_group_title= 'Regulations' WHERE configuration_group_id = '11';

DELETE FROM configuration WHERE configuration_key = 'BOX_WIDTH_LEFT';
DELETE FROM configuration WHERE configuration_key = 'BOX_WIDTH_RIGHT';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,
last_modified, date_added, use_function, set_function) VALUES ('Column Width - Left Boxes', 'BOX_WIDTH_LEFT', '150px', 'Width of the Left Column Boxes<br />px may
be included<br />Default = 150px', 19, 1, NULL, '2003-11-21 22:16:36', NULL, NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,
last_modified, date_added, use_function, set_function) VALUES ('Column Width - Right Boxes', 'BOX_WIDTH_RIGHT', '150px', 'Width of the Right Column Boxes<br />px
may be included<br />Default = 150px', 19, 2, NULL, '2003-11-21 22:16:36', NULL, NULL);

DELETE FROM configuration WHERE configuration_key = 'MAX_ROW_LISTS_ATTRIBUTES';

DELETE FROM configuration WHERE configuration_key = 'DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE';
DELETE FROM configuration WHERE configuration_key = 'DOWNLOADS_CONTROLLER_ORDERS_STATUS';
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'Downloads Controller Update Status Value', 'DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE', '4', 'What orders_status resets the Download days and Max Downloads - Default is 4', '13', '10', now(), '', NULL , NULL);
INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('', 'Downloads Controller Order Status Value', 'DOWNLOADS_CONTROLLER_ORDERS_STATUS', '2', 'Downloads Controller Order Status Value - Default=2', '13', '12', now(), '', NULL , NULL);

DELETE FROM configuration WHERE configuration_key = 'PRODUCT_LIST_BUY_NOW';

ALTER TABLE salemaker_sales CHANGE sale_categories_selected sale_categories_selected TEXT DEFAULT NULL;
ALTER TABLE salemaker_sales CHANGE sale_categories_all sale_categories_all TEXT DEFAULT NULL;

ALTER TABLE configuration CHANGE configuration_title configuration_title TEXT NOT NULL;
ALTER TABLE configuration CHANGE configuration_value configuration_value TEXT DEFAULT NULL;
ALTER TABLE configuration CHANGE configuration_description configuration_description TEXT DEFAULT NULL;

UPDATE configuration SET set_function = 'zen_cfg_textarea(' WHERE configuration_key= 'CONTACT_US_LIST';

DELETE FROM configuration WHERE configuration_key = 'ORDER_WEIGHT_ZERO_STATUS';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Order Free Shipping 0 Weight Status', 'ORDER_WEIGHT_ZERO_STATUS', '1', 'If there is no weight to the order, does the order have Free Shipping?<br />0= no<br />1= yes', '7', '15', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

#INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Main Page - Opens with Category', 'CATEGORIES_START_MAIN', '', 'Blank= None - open to main page<br />0= Top Level Categories<br />Or enter the Category ID#<br />Note: Sub Categories can also be used Example: 3_10', '19', '45', '', '', now());

DELETE FROM configuration WHERE configuration_key = 'CATEGORIES_COUNT_PREFIX';
DELETE FROM configuration WHERE configuration_key = 'CATEGORIES_COUNT_SUFFIX';
DELETE FROM configuration WHERE configuration_key = 'CATEGORIES_COUNT_ZERO';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Count Prefix', 'CATEGORIES_COUNT_PREFIX', '&nbsp;(', 'What do you want to Prefix the count with?<br />Default= (', 19, 27, NULL, '2003-01-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Categories Count Suffix', 'CATEGORIES_COUNT_SUFFIX', ')', 'What do you want as a Suffix to the count?<br />Default= )', 19, 28, NULL, '2003-01-21 22:16:36', NULL, 'zen_cfg_textarea_small(');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories with 0 Products Status', 'CATEGORIES_COUNT_ZERO', '1', 'Show Category Count for 0 Products?<br />0= off<br />1= on', 19, 30, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

DELETE FROM configuration WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_LISTING';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Products Listing- Number Per Page', 'MAX_DISPLAY_PRODUCTS_LISTING', '15', 'Maximum Number of Products to list per page on main page', '3', '30', now());

DELETE FROM configuration WHERE configuration_key = 'SHOW_TOTALS_IN_CART';
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Shopping Cart - Show Totals', 'SHOW_TOTALS_IN_CART', '1', 'Show Totals Above Shopping Cart?<br />0= off<br />1= on', 19, 31, 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

UPDATE configuration SET configuration_title= 'Product Info - Show Option Values and Attributes Images for Radio Buttons and Checkboxes' WHERE configuration_key = 'PRODUCT_IMAGES_ATTRIBUTES_NAMES_COLUMN';

INSERT INTO admin VALUES ('', 'demo', 'demo@localhost', '23ce1aad0e04a3d2334c7aef2f8ade83:58', 2);

UPDATE configuration SET configuration_description= 'Shopping Cart Box Shows<br />0= Always<br />1= Only when full or GV Balance<br />2= Only when full or GV Balance, but not when viewing the Shopping Cart page itself' WHERE configuration_key= 'SHOW_SHOPPING_CART_BOX_STATUS';

## END OF UPDATE