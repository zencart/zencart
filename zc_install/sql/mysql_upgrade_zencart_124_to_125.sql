# This SQL script upgrades the core Zen Cart database structure from v1.2.4 to v1.2.5
#
# $Id: mysql_upgrade_zencart_124_to_125.sql 18695 2011-05-04 05:24:19Z drbyte $
#

## CONFIGURATION TABLE
UPDATE configuration set configuration_title='Download Expiration (Number of Days)' WHERE configuration_key='DOWNLOAD_MAX_DAYS';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allowed Filename Extensions for uploading', 'UPLOAD_FILENAME_EXTENSIONS', 'jpg,jpeg,gif,png,eps,cdr,ai,pdf,tif,tiff,bmp,zip', 'List the permissible filetypes (filename extensions) to be allowed when files are uploaded to your site by customers. Separate multiple values with commas(,). Do not include the dot(.).<br /><br />Suggested setting: "jpg,jpeg,gif,png,eps,cdr,ai,pdf,tif,tiff,bmp,zip"', '3', '61', 'zen_cfg_textarea(', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('IP to Host Conversion Status', 'SESSION_IP_TO_HOST_ADDRESS', 'true', 'Convert IP Address to Host Address<br /><br />Note: on some servers this can slow down the initial start of a session or execution of Emails', '15', '10', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', '0', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '8', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_NEW_LISTING_MULTIPLE_ADD_TO_CART', '0', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '21', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART', '0', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '22', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Display Multiple Products Qty Box Status and Set Button Location', 'PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART', '0', 'Do you want to display Add Multiple Products Qty Box and Set Button Location?<br />0= off<br />1= Top<br />2= Bottom<br />3= Both', '23', '25', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Orders Detail Display on Admin Orders Listing', 'MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING', '0', 'Maximum number of Order Details<br />0 = Unlimited', 3, 65, now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Discount Coupons Per Page', 'MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS', '20', 'Number of Discount Coupons to list per Page', '16', '81', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Discount Coupon Report Results Per Page', 'MAX_DISPLAY_SEARCH_RESULTS_DISCOUNT_COUPONS_REPORTS', '20', 'Number of Discount Coupons to list on Reports Page', '16', '81', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Group - Side Box banner_box_all', 'SHOW_BANNERS_GROUP_SET_ALL', 'BannersAll', 'The Banner Display Group may only be from one (1) Banner Group for the Banner All sidebox<br /><br />Default Group is BannersAll<br /><br />What Banner Group do you want to use in the Side Box - banner_box_all?<br />Leave blank for none', '19', '72', '', '', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Categories/Products Display Sort Order', 'CATEGORIES_PRODUCTS_SORT_ORDER', '0', 'Categories/Products Display Sort Order<br />0= Products Sort Order/Name<br />1= Products Name<br />2= Products Model', '19', '100', 'zen_cfg_select_option(array(\'0\', \'1\', \'2\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Sales Tax Display Status', 'STORE_TAX_DISPLAY_STATUS', '0', 'Always show Sales Tax even when amount is $0.00?<br />0= Off<br />1= On', '1', '21', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Display Product Description', 'PRODUCT_LIST_DESCRIPTION', '0', 'Do you want to display the Product Description?<br /><br />0= OFF<br />150= Suggested Length, or enter the maximum number of characters to display', '8', '30', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Send Copy of Pending Reviews Emails To - Status', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS', '0', 'Send copy of Pending Reviews Status<br />0= off 1= on', '12', '25', 'zen_cfg_select_option(array(\'0\', \'1\'),', now());
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Send Copy of Pending Reviews Emails To', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', '', 'Send copy of Pending Reviews emails to the following email addresses, in this format: Name 1 &lt;email@address1&gt;, Name 2 &lt;email@address2&gt;', '12', '26', now());


ALTER TABLE banners ADD COLUMN banners_sort_order INT(11) DEFAULT '0' NOT NULL AFTER banners_on_ssl;

ALTER TABLE orders_products_attributes CHANGE COLUMN products_attributes_weight products_attributes_weight FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products CHANGE COLUMN products_weight products_weight FLOAT DEFAULT '0' NOT NULL;
ALTER TABLE products_attributes CHANGE COLUMN products_attributes_weight products_attributes_weight FLOAT DEFAULT '0' NOT NULL;


ALTER TABLE orders ADD COLUMN ip_address varchar(15) NOT NULL default '' ;

UPDATE configuration SET configuration_description='Customer must be Authorized to shop<br />0= Not required<br />1= Must be Authorized to Browse<br />2= May browse but no prices unless Authorized<br />3= Customer May Browse and May see Prices but Must be Authorized to Buy<br /><br />It is recommended that Option 2 or 3 be used for the purposes of Spiders', set_function='zen_cfg_select_option(array(\'0\', \'1\', \'2\', \'3\'), ' WHERE configuration_key='CUSTOMERS_APPROVAL_AUTHORIZATION';

UPDATE configuration SET configuration_description='Enter the Postal Code (ZIP) of the Store to be used in shipping quotes. NOTE: For USA zip codes, only use your 5 digit zip code.' WHERE configuration_key='SHIPPING_ORIGIN_ZIP';


### bof: meta tags database updates and changes
# update products table structure
ALTER TABLE products ADD COLUMN metatags_title_status TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE products ADD COLUMN metatags_products_name_status TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE products ADD COLUMN metatags_model_status TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE products ADD COLUMN metatags_price_status TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE products ADD COLUMN metatags_title_tagline_status TINYINT(1) DEFAULT '0' NOT NULL;

# add new meta_tags_products_description
DROP TABLE IF EXISTS meta_tags_products_description;
CREATE TABLE meta_tags_products_description (
  products_id int(11) NOT NULL auto_increment,
  language_id int(11) NOT NULL default '1',
  metatags_title VARCHAR(255) NOT NULL default '',
  metatags_keywords TEXT,
  metatags_description TEXT,
  PRIMARY KEY  (products_id,language_id)
);


#insert product type layout settings
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Title', 'SHOW_PRODUCT_INFO_METATAGS_TITLE_STATUS', '1', 'Display Product Title in Meta Tags Title 0= off 1= on', '1', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Name', 'SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Display Product Name in Meta Tags Title 0= off 1= on', '1', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Model', 'SHOW_PRODUCT_INFO_METATAGS_MODEL_STATUS', '1', 'Display Product Model in Meta Tags Title 0= off 1= on', '1', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Price', 'SHOW_PRODUCT_INFO_METATAGS_PRICE_STATUS', '1', 'Display Product Price in Meta Tags Title 0= off 1= on', '1', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Tagline', 'SHOW_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Product Tagline in Meta Tags Title 0= off 1= on', '1', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Title', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_STATUS', '1', 'Display Product Title in Meta Tags Title 0= off 1= on', '2', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Name', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Display Product Name in Meta Tags Title 0= off 1= on', '2', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Model', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_MODEL_STATUS', '1', 'Display Product Model in Meta Tags Title 0= off 1= on', '2', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Price', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRICE_STATUS', '1', 'Display Product Price in Meta Tags Title 0= off 1= on', '2', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Tagline', 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Product Tagline in Meta Tags Title 0= off 1= on', '2', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Title', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_STATUS', '1', 'Display Document Title in Meta Tags Title 0= off 1= on', '3', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Name', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Display Document Name in Meta Tags Title 0= off 1= on', '3', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Tagline', 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Document Tagline in Meta Tags Title 0= off 1= on', '3', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Title', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_STATUS', '1', 'Display Document Title in Meta Tags Title 0= off 1= on', '4', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Name', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Display Document Name in Meta Tags Title 0= off 1= on', '4', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Model', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_MODEL_STATUS', '1', 'Display Document Model in Meta Tags Title 0= off 1= on', '4', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Price', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRICE_STATUS', '1', 'Display Document Price in Meta Tags Title 0= off 1= on', '4', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Document Tagline', 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Document Tagline in Meta Tags Title 0= off 1= on', '4', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Title', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_STATUS', '1', 'Display Product Title in Meta Tags Title 0= off 1= on', '5', '50', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Name', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRODUCTS_NAME_STATUS', '1', 'Display Product Name in Meta Tags Title 0= off 1= on', '5', '51', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Model', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_MODEL_STATUS', '1', 'Display Product Model in Meta Tags Title 0= off 1= on', '5', '52', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Price', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRICE_STATUS', '1', 'Display Product Price in Meta Tags Title 0= off 1= on', '5', '53', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show Metatags Title Default - Product Tagline', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Product Tagline in Meta Tags Title 0= off 1= on', '5', '54', 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
### eof: meta tags database updates and changes

### products to multiple categories linker
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Display Columns Products to Multiple Categories Manager', 'MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS', '3', 'Maximum Display Columns Products to Multiple Categories Manager<br />3 = Default', 3, 70, now());

### global add, delete, copy features for option names and values
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Option Names and Values Global Add, Copy and Delete Features Status', 'OPTION_NAMES_VALUES_GLOBAL_STATUS', '1', 'Option Names and Values Global Add, Copy and Delete Features Status<br />0= Hide Features<br />1= Show Features<br />2= Products Model', '19', '110', 'zen_cfg_select_option(array(\'0\', \'1\'), ', now());

## SMTPAUTH port
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('SMTP Email Mail Server Port', 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT', '25', 'Enter the IP port number that your SMTP mailserver operates on.<br />Only required if using SMTP Authentication for email.', '12', '101', now());
DELETE FROM configuration WHERE configuration_key = 'EMAIL_SMTPAUTH_SEND_DOMAIN';


ALTER TABLE orders_products_attributes CHANGE COLUMN products_options_values products_options_values VARCHAR(64) NOT NULL;
ALTER TABLE orders CHANGE COLUMN shipping_method shipping_method VARCHAR(128) NOT NULL;
ALTER TABLE orders CHANGE COLUMN payment_method payment_method VARCHAR(128) NOT NULL;
ALTER TABLE whos_online ADD COLUMN user_agent VARCHAR(64) NOT NULL default '';

#delete duplicate that's existed since at least v1.2.1
#NEXT_X_ROWS_AS_ONE_COMMAND:5
SET @t1=0;
SELECT (@t1:=configuration_id) as t1
FROM product_type_layout
WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_ARTIST' limit 1;
DELETE FROM configuration where configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_ARTIST' and configuration_id > @t1;
######

#Admin Activity log
CREATE TABLE admin_activity_log (
  log_id smallint(15) NOT NULL auto_increment,
  access_date datetime NOT NULL default '0001-01-01 00:00:00',
  admin_id int(11) NOT NULL default '0',
  page_accessed varchar(80) NOT NULL default '',
  page_parameters varchar(150) default NULL,
  ip_address varchar(15) NOT NULL default '',
  PRIMARY KEY  (log_id),
  KEY page_accessed (page_accessed),
  KEY access_date (access_date),
  KEY idx_ip_zen (ip_address)
);


### ADD INDEXES:
## On a large database, this could take some time, and sadly might encounter a timeout. 
## If a timeout occurs, simply run these commands manually...

ALTER TABLE banners_history ADD INDEX idx_banners_id_zen ( banners_id ) ;
ALTER TABLE banners ADD INDEX idx_status_group_zen ( status, banners_group ) ;

ALTER TABLE specials ADD INDEX idx_status_zen ( status ) ;
ALTER TABLE specials ADD INDEX idx_products_id_zen ( products_id ) ;
ALTER TABLE specials ADD INDEX idx_date_avail_zen ( specials_date_available ) ;

ALTER TABLE featured ADD INDEX idx_status_zen ( status ) ;
ALTER TABLE featured ADD INDEX idx_products_id_zen ( products_id ) ;
ALTER TABLE featured ADD INDEX idx_date_avail_zen ( featured_date_available ) ;

ALTER TABLE salemaker_sales ADD INDEX idx_sale_status_zen ( sale_status ) ;

ALTER TABLE categories ADD INDEX idx_categories_parent_id_TEST ( parent_id ) ;
ALTER TABLE categories ADD INDEX idx_parent_id_cat_id_zen ( parent_id, categories_id ) ;
ALTER TABLE categories ADD INDEX idx_status_zen ( categories_status );

ALTER TABLE product_types_to_category ADD INDEX idx_category_id_zen ( category_id ) ;
ALTER TABLE product_types_to_category ADD INDEX idx_product_type_id_zen ( product_type_id ) ;
ALTER TABLE product_types ADD INDEX idx_type_master_type_zen ( type_master_type ) ;

ALTER TABLE products ADD INDEX idx_products_status_zen ( products_status ) ;
#ALTER TABLE products ADD INDEX idx_prod_type_zen ( products_type );
ALTER TABLE products_to_categories ADD INDEX idx_cat_prod_id_zen (categories_id, products_id) ;
ALTER TABLE products_attributes ADD INDEX idx_id_options_id_values_zen ( products_id, options_id, options_values_id ) ;
#ALTER TABLE products_attributes ADD INDEX idx_attrib_display_only_zen ( attributes_display_only ) ;
#ALTER TABLE products_attributes ADD INDEX idx_price_base_incl_zen ( attributes_price_base_included ) ;
ALTER TABLE products_discount_quantity ADD INDEX idx_id_qty_zen ( products_id, discount_qty ) ;
ALTER TABLE products_options_values_to_products_options ADD INDEX idx_prod_opt_val_id_zen ( products_options_values_id ) ;
ALTER TABLE products_options ADD INDEX idx_lang_id_zen ( language_id ) ;

ALTER TABLE tax_rates ADD INDEX idx_tax_zone_id_zen ( tax_zone_id ) ;
ALTER TABLE tax_rates ADD INDEX idx_tax_class_id_zen ( tax_class_id ) ;

ALTER TABLE configuration ADD INDEX idx_key_value_zen ( configuration_key, configuration_value(10) ) ;
ALTER TABLE configuration_group ADD INDEX idx_visible_zen ( visible ) ;
ALTER TABLE configuration ADD INDEX idx_cfg_grp_id_zen ( configuration_group_id ) ;
ALTER TABLE product_type_layout ADD INDEX idx_key_value_zen ( configuration_key, configuration_value(10) ) ;

ALTER TABLE customers ADD INDEX idx_email_address_zen ( customers_email_address ) ;
ALTER TABLE customers ADD INDEX idx_referral_zen ( customers_referral(10) ) ;
ALTER TABLE customers ADD INDEX idx_grp_pricing_zen ( customers_group_pricing ) ;
ALTER TABLE customers ADD INDEX idx_nick_zen ( customers_nick ) ;
ALTER TABLE customers ADD INDEX idx_newsletter_zen ( customers_newsletter ) ;
ALTER TABLE customers_basket ADD INDEX idx_customers_id_zen ( customers_id ) ;
ALTER TABLE customers_basket_attributes ADD INDEX idx_cust_id_prod_id_zen ( customers_id, products_id(36) );

ALTER TABLE orders ADD INDEX idx_status_orders_cust_zen ( orders_status, orders_id, customers_id );
#ALTER TABLE orders ADD INDEX idx_customers_id_zen ( customers_id ) ;
ALTER TABLE orders_status_history ADD INDEX idx_orders_id_status_id_zen ( orders_id, orders_status_id ) ;
ALTER TABLE orders_products ADD INDEX idx_orders_id_zen ( orders_id ) ;
ALTER TABLE orders_products ADD INDEX orders_id_prod_id_zen ( orders_id , products_id ) ;
ALTER TABLE orders_products_attributes ADD INDEX idx_orders_id_prod_id_zen ( orders_id , orders_products_id ) ;
ALTER TABLE orders_products_download ADD INDEX idx_orders_id_zen ( orders_id );
ALTER TABLE orders_products_download ADD INDEX idx_orders_products_id_zen ( orders_products_id );

ALTER TABLE layout_boxes ADD INDEX idx_name_template_zen ( layout_template, layout_box_name ) ;

ALTER TABLE coupon_gv_queue ADD INDEX idx_release_flag_zen ( release_flag ) ;
ALTER TABLE coupons ADD INDEX idx_active_type_zen ( coupon_active, coupon_type ) ;
ALTER TABLE coupons_description DROP INDEX coupon_id;
ALTER TABLE coupons_description ADD PRIMARY KEY (coupon_id, language_id);
ALTER TABLE coupon_restrict ADD INDEX idx_coup_id_prod_id_zen (coupon_id, product_id);
ALTER TABLE coupon_redeem_track ADD INDEX idx_coupon_id_zen ( coupon_id ) ;
ALTER TABLE coupon_gv_queue DROP INDEX uid;
ALTER TABLE coupon_gv_queue ADD INDEX idx_cust_id_order_id_zen ( customer_id , order_id ) ;
ALTER TABLE coupon_email_track ADD INDEX idx_coupon_id_zen ( coupon_id ) ;

ALTER TABLE reviews ADD INDEX idx_products_id_zen ( products_id ) ;
ALTER TABLE reviews ADD INDEX idx_customers_id_zen ( customers_id ) ;

ALTER TABLE admin ADD INDEX idx_admin_name_zen ( admin_name ) ;

ALTER TABLE files_uploaded ADD INDEX idx_customers_id_zen ( customers_id ) ;

ALTER TABLE email_archive DROP INDEX email_to;
ALTER TABLE email_archive ADD INDEX idx_email_to_address_zen ( email_to_address ) ;
ALTER TABLE email_archive DROP INDEX module ;
ALTER TABLE email_archive ADD INDEX idx_module_zen ( module ) ;

ALTER TABLE media_to_products ADD INDEX idx_media_product_zen ( media_id, product_id ) ;
ALTER TABLE media_clips ADD INDEX idx_media_id_zen ( media_id ) ;
ALTER TABLE product_music_extra ADD INDEX idx_music_genre_id_zen ( music_genre_id ) ;

ALTER TABLE paypal ADD INDEX idx_zen_order_id_zen ( zen_order_id ) ;
ALTER TABLE paypal_payment_status_history ADD INDEX idx_paypal_ipn_id_zen ( paypal_ipn_id ) ;
ALTER TABLE paypal_session ADD INDEX idx_session_id_zen ( session_id(36) ) ;

#############

#### VERSION UPDATE COMMANDS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version's info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='2.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.4->1.2.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='2.5', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.2.4->1.2.5', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';


#####  END OF UPGRADE SCRIPT