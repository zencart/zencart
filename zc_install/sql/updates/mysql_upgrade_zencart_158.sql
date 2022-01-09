#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.7 to v1.5.8
# *
# * @access private
# * @copyright Copyright 2003-2021 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id:  New in v1.5.8 $
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

# Clear out active customer sessions. Truncating helps the database clean up behind itself.
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;

ALTER TABLE layout_boxes ADD plugin_details varchar(100) NOT NULL default '';
ALTER TABLE manufacturers ADD COLUMN featured tinyint default 0;
ALTER TABLE customers ADD registration_ip varchar(45) NOT NULL default '';
ALTER TABLE customers ADD last_login_ip varchar(45) NOT NULL default '';
ALTER TABLE customers_info ADD INDEX idx_date_created_cust_id_zen (customers_info_date_account_created, customers_info_id);

ALTER TABLE orders_products MODIFY products_name varchar(191) NOT NULL default '';
ALTER TABLE products_description MODIFY products_name varchar(191) NOT NULL default '';

ALTER TABLE orders MODIFY customers_country varchar(64) NOT NULL default ''; 
ALTER TABLE orders MODIFY delivery_country varchar(64) NOT NULL default ''; 
ALTER TABLE orders MODIFY billing_country varchar(64) NOT NULL default ''; 

# Remove greater-than sign in query_builder
UPDATE query_builder SET query_name = 'Customers Dormant for 3+ months (Subscribers)' WHERE query_id = 3;

# Remove deprecated defines
DELETE FROM configuration WHERE configuration_key = 'CATEGORIES_SPLIT_DISPLAY';
DELETE FROM configuration WHERE configuration_key = 'CUSTOMERS_AUTHORIZATION_PRICES_OFF';
DELETE FROM configuration WHERE configuration_key = 'EMAIL_FRIENDLY_ERRORS';
DELETE FROM configuration WHERE configuration_key = 'EMAIL_LINEFEED';
DELETE FROM configuration WHERE configuration_key = 'CC_CVV_MIN_LENGTH';
DELETE FROM configuration WHERE configuration_key = 'MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER';


# Update configuration descriptions
UPDATE configuration SET configuration_description = 'Enter your PayPal Merchant ID here. This is used for the more user-friendly In-Context checkout mode. You can obtain this value by going to your PayPal account, clicking on your account name at the top right, then clicking Account Settings, and navigating to the Business Information section; You will find your Merchant Account ID on that screen. A typical Merchant ID looks like FDEFDEFDEFDE11.' WHERE configuration_key = 'MODULE_PAYMENT_PAYPALWPP_MERCHANTID';
UPDATE configuration SET configuration_description = 'If there is no weight to the order, does the order have Free Shipping?<br>0= no<br>1= yes<br><br>Note: When using Free Shipping, Enable the Free Shipping Module.  It will only show when shipping is free.' WHERE configuration_key = 'ORDER_WEIGHT_ZERO_STATUS';
UPDATE configuration SET configuration_title = 'Category Header Menu ON/OFF', configuration_description = 'Category Header Nav<br />This enables the display of your store\'s categories as a menu across the top of your header. There are many potential creative uses for this.<br />0= Hide Categories Tabs<br />1= Show Categories Tabs' WHERE configuration_key = 'CATEGORIES_TABS_STATUS';

UPDATE configuration SET configuration_description = 'Defines the method for sending mail.<br><br><strong>PHP</strong> is the default, and uses built-in PHP wrappers for processing.<br><strong>smtpauth</strong> should be used by most sites, as it provides secure sending of authenticated email. You must also configure your smtpauth settings in the appropriate fields in this admin section.<br><strong>Gmail</strong> is used for sending emails using the Google mail service, and requires the [less secure] setting enabled in your gmail account.<br><strong>sendmail</strong> is for linux/unix hosts using the sendmail program on the server<br><strong>sendmail-f</strong> is only for servers which require the use of the -f parameter to use sendmail. This is a security setting often used to prevent spoofing. Will cause errors if your host mailserver is not configured to use it.<br><strong>Qmail</strong> is used for linux/unix hosts running Qmail as sendmail wrapper at /var/qmail/bin/sendmail.<br><br>MOST SITES WILL USE [smtpauth].', set_function='zen_cfg_select_option(array(\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Gmail\',\'Qmail\'),' WHERE configuration_key = 'EMAIL_TRANSPORT';


UPDATE configuration SET configuration_description = 'Customers Referral Code is created from<br />0= Off<br />1= 1st Discount Coupon Code used<br />2= Customer can add during create account or edit if blank<br /><br />NOTE: Once the Customers Referral Code has been set it can only be changed by the Administrator' WHERE configuration_key = 'CUSTOMERS_REFERRAL_STATUS';

UPDATE configuration SET configuration_description = 'The shipping cost may be calculated based on the total weight of the items ordered, the total price of the items ordered, or the total number of items ordered.' WHERE configuration_key = 'MODULE_SHIPPING_TABLE_MODE';

UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing an index listing' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_LISTING';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing All Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_ALL';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing New Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_NEW';
UPDATE configuration SET configuration_description = 'Number of products to show per page when viewing Featured Products' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS';

UPDATE configuration SET configuration_title = 'New Products Centerbox', configuration_description = 'Number of products to display in the New Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_NEW_PRODUCTS';
UPDATE configuration SET configuration_title = 'Products on Special Centerbox', configuration_description = 'Number of products to display in the Products on Special centerbox' WHERE configuration_key = 'MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX';
UPDATE configuration SET configuration_title = 'Upcoming Products Centerbox', configuration_description = 'Number of products to display in the Upcoming Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_UPCOMING_PRODUCTS';
UPDATE configuration SET configuration_title = 'Featured Products Centerbox', configuration_description = 'Number of products to display in the Featured Products centerbox' WHERE configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS_FEATURED';

UPDATE configuration SET configuration_title = 'Products on Special Page', configuration_description = 'Number of products to display per page on the Specials page' WHERE configuration_key = 'MAX_DISPLAY_SPECIAL_PRODUCTS';
UPDATE configuration SET configuration_title = 'All Products Page', configuration_description = 'Number of products to display per page on the All Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_ALL';
UPDATE configuration SET configuration_title = 'Featured Products Page', configuration_description = 'Number of products to display per page on the Featured Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS';
UPDATE configuration SET configuration_title = 'New Products Page', configuration_description = 'Number of products to display per page on the New Products page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_NEW';
UPDATE configuration SET configuration_title = 'Products Listing Page', configuration_description = 'Number of products to display per page on a Listing page' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_LISTING';

#############
# Incorporate setting for Column-Grid-Layout template control
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Columns Per Row', 'PRODUCT_LISTING_COLUMNS_PER_ROW', '1', 'Select the number of columns of products to show per row in the product listing.<br>Recommended: 3<br>1=[rows] mode.', '8', '45', NULL, now(), NULL, NULL);


#############

INSERT IGNORE INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('customerGroups', 'BOX_CUSTOMERS_CUSTOMER_GROUPS', 'FILENAME_CUSTOMER_GROUPS', '', 'customers', 'Y', 3);

CREATE TABLE customer_groups (
  group_id int UNSIGNED NOT NULL AUTO_INCREMENT,
  group_name varchar(191) NOT NULL,
  group_comment varchar(255),
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (group_id),
  UNIQUE KEY idx_groupname_zen (group_name)
);
CREATE TABLE customers_to_groups (
  id int UNSIGNED NOT NULL AUTO_INCREMENT,
  group_id int UNSIGNED NOT NULL,
  customer_id int UNSIGNED NOT NULL,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY idx_custid_groupid_zen (customer_id, group_id),
  KEY idx_groupid_custid_zen (group_id, customer_id)
);

#############

### Added in v157a, including here in case upgrades missed it
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Columns Per Row', 'PRODUCT_LISTING_COLUMNS_PER_ROW', '1', 'Select the number of columns of products to show per row in the product listing.<br>Recommended: 3<br>1=[rows] mode.', '8', '45', NULL, now(), NULL, NULL);
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Disabled Product Status for Search Engines', 'DISABLED_PRODUCTS_TRIGGER_HTTP200', 'false', 'When a product is marked Disabled (status=0) but is not deleted from the database, should Search Engines still show it as Available?<br>eg:<br>True = Return HTTP 200 response<br>False = Return HTTP 410<br>(Deleting it will return HTTP 404)<br><b>Default: false</b>', '9', '10', 'zen_cfg_select_option(array(\'true\', \'false\'),', now());
#############


#############
### Added v158 bring address formats up to date
### Updated address formats
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$postcode $city $statebrackets$cr$country' , address_summary = '$postcode $city $statebrecket'  WHERE address_format_id = 1;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city $state    $postcode$cr$country' , address_summary = '$city $state $postcode' WHERE address_format_id = 2;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$postcode $city $state$cr$country' , address_summary = '$postcode $city $state' WHERE address_format_id = 3;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city $postcode$cr$country' , address_summary = '$city $postcode' WHERE address_format_id = 4;
UPDATE address_format SET address_format = '$firstname $lastnameupper$cr$streets$cr$postcode  $city$cr$country' , address_summary = '$postcode $city' WHERE address_format_id = 5;
UPDATE address_format SET address_format = '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country' , address_summary = '$city / $state / $postcode' WHERE address_format_id = 6 ;
UPDATE address_format SET address_format = '$firstname $lastname$cr$city$cr$streets$cr$postcode$cr$country' , address_summary = '$city $street / $postcode' WHERE address_format_id = 7;

###Add new address formats
INSERT INTO address_format VALUES (8,'$firstname $lastname$cr$streets$cr$city $state$cr$postcode$cr$country','$city $state / $postcode');
INSERT INTO address_format VALUES (9,'$firstname $lastname$cr$streets$cr$postcode$cr$city $state$cr$country','$postcode / $city / $state');
INSERT INTO address_format VALUES (10,'$firstname $lastname$cr$streets$cr$city $postcode$cr$state$cr$country','$city $postcode / $state');
INSERT INTO address_format VALUES (11,'$firstname $lastname$cr$streets$cr$postcode $city$cr$state$cr$country','$postcode $city / $state');
INSERT INTO address_format VALUES (12,'$firstname $lastname$cr$streets$cr$postcode$cr$city$cr$state$cr$country','$postcode / $city / $state');
INSERT INTO address_format VALUES (13,'$firstname $lastname$cr$streets$cr$city $postcode $state$cr$country',' $city $postcode $state');
INSERT INTO address_format VALUES (14,'$firstname $lastname$cr$streets$cr$city$cr$postcode $state$cr$country',' $city / $postcode $state');
INSERT INTO address_format VALUES (15,'$firstname $lastname$cr$streets$cr$city$cr$state $postcode$cr$country','$city / $state $postcode');

### Update countries with new address formats
UPDATE countries SET address_format_id = '5' WHERE countries_id = 240;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 1;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 2;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 3;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 4;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 5;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 6;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 7;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 8;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 9;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 10;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 11;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 12;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 13;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 15;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 16;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 17;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 18;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 19;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 20;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 22;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 23;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 24;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 25;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 26;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 27;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 28;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 29;
UPDATE countries SET address_format_id = '8' WHERE countries_id = 30;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 31;
UPDATE countries SET address_format_id = '15' WHERE countries_id = 32;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 33;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 34;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 35;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 36;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 37;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 39;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 40;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 41;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 42;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 43;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 44;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 45;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 46;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 47;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 48;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 49;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 50;
UPDATE countries SET address_format_id = '8' WHERE countries_id = 51;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 52;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 53;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 54;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 55;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 56;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 57;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 58;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 59;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 60;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 61;
UPDATE countries SET address_format_id = '9' WHERE countries_id = 62;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 63;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 64;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 65;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 66;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 67;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 68;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 69;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 70;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 71;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 72;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 73;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 75;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 76;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 77;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 78;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 79;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 80;
UPDATE countries SET address_format_id = '8' WHERE countries_id = 82;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 83;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 84;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 85;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 86;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 87;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 88;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 89;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 90;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 91;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 92;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 93;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 94;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 95;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 96;
UPDATE countries SET address_format_id = '7' WHERE countries_id = 97;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 98;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 99;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 100;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 101;
UPDATE countries SET address_format_id = '8' WHERE countries_id = 102;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 103;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 104;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 105;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 106;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 107;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 108;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 109;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 110;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 111;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 112;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 113;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 114;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 115;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 116;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 117;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 118;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 119;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 120;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 121;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 122;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 123;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 124;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 125;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 126;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 127;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 128;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 129;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 130;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 131;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 132;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 133;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 134;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 135;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 136;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 137;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 138;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 139;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 140;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 141;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 142;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 143;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 144;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 145;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 146;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 147;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 148;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 149;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 151;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 152;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 153;
UPDATE countries SET address_format_id = '9' WHERE countries_id = 154;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 155;
UPDATE countries SET address_format_id = '10' WHERE countries_id = 156;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 157;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 158;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 159;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 160;
UPDATE countries SET address_format_id = '12' WHERE countries_id = 161;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 162;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 163;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 164;
UPDATE countries SET address_format_id = '13' WHERE countries_id = 165;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 166;
UPDATE countries SET address_format_id = '9' WHERE countries_id = 167;
UPDATE countries SET address_format_id = '14' WHERE countries_id = 168;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 169;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 170;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 171;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 172;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 173;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 174;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 175;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 176;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 177;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 178;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 179;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 180;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 181;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 182;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 183;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 184;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 185;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 186;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 187;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 189;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 190;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 191;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 192;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 193;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 194;
UPDATE countries SET address_format_id = '1' WHERE countries_id = 195;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 196;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 197;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 198;
UPDATE countries SET address_format_id = '9' WHERE countries_id = 199;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 200;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 201;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 202;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 204;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 205;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 206;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 207;
UPDATE countries SET address_format_id = '11' WHERE countries_id = 208;
UPDATE countries SET address_format_id = '8' WHERE countries_id = 209;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 210;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 211;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 212;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 213;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 214;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 215;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 216;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 217;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 218;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 219;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 220;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 221;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 224;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 225;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 226;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 227;
UPDATE countries SET address_format_id = '3' WHERE countries_id = 228;
UPDATE countries SET address_format_id = '13' WHERE countries_id = 229;
UPDATE countries SET address_format_id = '15' WHERE countries_id = 230;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 231;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 232;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 233;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 234;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 235;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 236;
UPDATE countries SET address_format_id = '4' WHERE countries_id = 238;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 239;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 241;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 242;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 243;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 244;
UPDATE countries SET address_format_id = '6' WHERE countries_id = 245;
UPDATE countries SET address_format_id = '5' WHERE countries_id = 246;
UPDATE countries SET address_format_id = '2' WHERE countries_id = 247;

################

#### Added in case was missed on upgrades.  also modified to allow for IgnoreDups in case someone had earlier version installed.
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Admin)?', 'REPORT_ALL_ERRORS_ADMIN', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', 10, 40, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors (Store)?', 'REPORT_ALL_ERRORS_STORE', 'No', 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', 10, 41, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),');
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function) VALUES ('Report All Errors: Backtrace on Notice Errors?', 'REPORT_ALL_ERRORS_NOTICE_BACKTRACE', 'No', 'Include backtrace information on &quot;Notice&quot; errors?  These are usually isolated to the identified file and the backtrace information just fills the logs. Default (<b>No</b>).', 10, 42, now(), NULL, 'zen_cfg_select_option(array(\'Yes\', \'No\'),');
UPDATE configuration SET configuration_description = 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart admin\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.', set_function = 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),' WHERE configuration_key = 'REPORT_ALL_ERRORS_ADMIN';
UPDATE configuration SET configuration_description = 'Do you want create debug-log files for <b>all</b> PHP errors, even warnings, that occur during your Zen Cart store\'s processing?  If you want to log all PHP errors <b>except</b> duplicate-language definitions, choose <em>IgnoreDups</em>.<br /><br /><strong>Note:</strong> Choosing \'Yes\' is not suggested for a <em>live</em> store, since it will reduce performance significantly!', set_function = 'zen_cfg_select_option(array(\'Yes\', \'No\', \'IgnoreDups\'),' WHERE configuration_key = 'REPORT_ALL_ERRORS_STORE';
############


#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.8-dev', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.7->1.5.8-dev', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.8-dev', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.7->1.5.8-dev', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
