#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.8 to v2.0.0
# *
# * @access private
# * @copyright Copyright 2003-2024 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: Scott Wilson 2024 Mar 16 Modified in v2.0.0-rc2 $
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


#PROGRESS_FEEDBACK:!TEXT=Updating Country/Zone tables ...

#############
#### Updated country information that has changed.
UPDATE countries SET countries_name = 'Türkiye' WHERE countries_iso_code_3 = 'TUR';
#############

#############
#### Updated zone names.

### NOTE: while it may not read very human-friendly, line-breaks have been inserted before FROM and INNER JOIN keywords
###       so that the zc_install parser can properly apply the table-name prefixes if they exist

### Switzerland
DELETE z
FROM zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id WHERE c.countries_iso_code_3 = 'CHE';

### Austria
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Vorarlberg' WHERE c.countries_iso_code_3 = 'AUT' AND z.zone_code = 'VB';

### Italia
INSERT INTO zones ( zone_country_id, zone_code, zone_name ) SELECT * FROM
(SELECT countries_id AS zone_country_id, 'SU' AS zone_code, 'Sud Sardegna' AS zone_name
FROM countries WHERE countries_iso_code_3 = 'ITA' LIMIT 1) AS tmp WHERE NOT EXISTS (SELECT *
FROM zones WHERE zone_country_id = (SELECT countries_id
FROM countries WHERE countries_iso_code_3 = 'ITA' LIMIT 1) AND zone_code = 'SU' LIMIT 1)
LIMIT 1;

UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Valle D\'Aosta' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'AO';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Barletta-Andria-Trani' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'BT';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Forlì-Cesena' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'FC';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'L\'Aquila' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'AQ';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Massa-Carrara' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'MS';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Pesaro E Urbino' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'PU';
UPDATE zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id SET z.zone_name = 'Verbano-Cusio-Ossola' WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'VB';
DELETE z
FROM zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'CI';
DELETE z
FROM zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'VS';
DELETE z
FROM zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'OG';
DELETE z
FROM zones z
INNER JOIN countries c ON z.zone_country_id = c.countries_id WHERE c.countries_iso_code_3 = 'ITA' AND z.zone_code = 'OT';
#############


#PROGRESS_FEEDBACK:!TEXT=Updating configuration setting choices

## Fix typos in old configuration keys
UPDATE product_type_layout SET configuration_description='Default setting for a new product (can be modified per product).<br>Show the Product Title in the page &lt;title&gt; tag.' WHERE configuration_key='SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_description='Default setting for a new product (can be modified per product).<br>Show the Product Model in the page &lt;title&gt; tag.' WHERE configuration_key='SHOW_PRODUCT_MUSIC_INFO_METATAGS_MODEL_STATUS';
UPDATE product_type_layout SET configuration_description='Default setting for a new product (can be modified per product).<br>Show the Product Price in the page &lt;title&gt; tag.' WHERE configuration_key='SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRICE_STATUS';
UPDATE product_type_layout SET configuration_description='Default setting for a new product (can be modified per product).<br>Show the defined constant "SITE_TAGLINE" in the page &lt;title&gt; tag.' WHERE configuration_key='SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_TAGLINE_STATUS';


## SNAF product listing changes
UPDATE configuration SET configuration_title = 'Sort Order Default - Product Listing' WHERE configuration_key = 'PRODUCT_LISTING_DEFAULT_SORT_ORDER';
UPDATE configuration SET configuration_group_id = 8, sort_order = 15, configuration_title = 'Sort Order Default - New Products' WHERE configuration_key = 'PRODUCT_NEW_LIST_SORT_DEFAULT';
UPDATE configuration SET configuration_group_id = 8, sort_order = 15, configuration_title = 'Sort Order Default - Featured Products' WHERE configuration_key = 'PRODUCT_FEATURED_LIST_SORT_DEFAULT';
UPDATE configuration SET configuration_group_id = 8, sort_order = 15, configuration_title = 'Sort Order Default - All-Products page' WHERE configuration_key = 'PRODUCT_ALL_LIST_SORT_DEFAULT';
UPDATE configuration SET configuration_group_id = 8, sort_order = 19 WHERE configuration_key = 'SHOW_NEW_PRODUCTS_UPCOMING_MASKED';
UPDATE configuration_group SET visible = 0 WHERE configuration_group_id = 21;
UPDATE configuration_group SET visible = 0 WHERE configuration_group_id = 22;
UPDATE configuration_group SET visible = 0 WHERE configuration_group_id = 23;
UPDATE admin_pages SET display_on_menu = 'N' WHERE page_key = 'configNewListing';
UPDATE admin_pages SET display_on_menu = 'N' WHERE page_key = 'configFeaturedListing';
UPDATE admin_pages SET display_on_menu = 'N' WHERE page_key = 'configAllListing';

## Clarify SHIPPING configuration examples.
UPDATE configuration SET configuration_description = 'What is the weight of typical packaging of small to medium packages?<br>Example:<br>Unit = Your SHIPPING_WEIGHT_UNITS (lbs or kgs) <br> 10% + 1 Unit 10:1<br>10% + 0 Units 10:0<br>0% + 5 Units 0:5<br>0% + 1/2 Unit 0:0.5<br>0% + 0 Units 0:0' WHERE configuration_key = 'SHIPPING_BOX_WEIGHT';
UPDATE configuration SET configuration_description = 'What is the weight of typical packaging for Large packages?<br>Example:<br>Unit = Your SHIPPING_WEIGHT_UNITS (lbs or kgs) <br> 10% + 1 Unit 10:1<br>10% + 0 Units 10:0<br>0% + 5 Units 0:5<br>0% + 1/2 Unit 0:0.5<br>0% + 0 Units 0:0' WHERE configuration_key = 'SHIPPING_BOX_PADDING';

# Change minimum dob field length for new date VALIDATION
UPDATE configuration SET configuration_value = 8 WHERE configuration_key = 'ENTRY_DOB_MIN_LENGTH' AND configuration_value=10;

# Add template_settings field
ALTER TABLE template_select ADD template_settings LONGTEXT DEFAULT NULL;


#PROGRESS_FEEDBACK:!TEXT=Updating Coupon features

DROP TABLE IF EXISTS coupon_referrers;
CREATE TABLE coupon_referrers (
  referrer_id int(11) NOT NULL AUTO_INCREMENT,
  referrer_domain varchar(64) NOT NULL,
  coupon_id INT(11) NOT NULL,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (referrer_id),
  UNIQUE KEY idx_referrer_domain_zen (referrer_domain),
  KEY idx_refcoupon_id_zen (coupon_id)
);
INSERT IGNORE INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('couponReferrers', 'BOX_COUPON_REFERRERS', 'FILENAME_COUPON_REFERRERS', '', 'gv', 'N', 5);



#############
#PROGRESS_FEEDBACK:!TEXT=Adding fields to Product table - may take some time

ALTER TABLE products ADD products_mpn varchar(32) DEFAULT NULL AFTER products_model;

# Product Dimensions fields. Modify(update) format if already present, or add if missing.
ALTER TABLE products MODIFY products_length DECIMAL(8,4) DEFAULT NULL;
ALTER TABLE products ADD products_length DECIMAL(8,4) DEFAULT NULL AFTER products_weight;
ALTER TABLE products MODIFY products_width DECIMAL(8,4) DEFAULT NULL;
ALTER TABLE products ADD products_width DECIMAL(8,4) DEFAULT NULL AFTER products_length;
ALTER TABLE products MODIFY products_height DECIMAL(8,4) DEFAULT NULL;
ALTER TABLE products ADD products_height DECIMAL(8,4) DEFAULT NULL AFTER products_width;
# rename field used in some plugins:
ALTER TABLE products CHANGE products_ready_to_ship product_ships_in_own_box TINYINT DEFAULT NULL;
ALTER TABLE products MODIFY product_ships_in_own_box TINYINT DEFAULT NULL;
ALTER TABLE products ADD product_ships_in_own_box TINYINT DEFAULT NULL AFTER products_height;
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Shipping Weight Units', 'SHIPPING_WEIGHT_UNITS', 'lbs', 'How should shipping modules treat the weights set on products? (remember if using lbs, 1 ounce=0.0625). <b>NOTE: You must still manually update your language files to show the correct units visually.</b>', 7, 6, now(), 'zen_cfg_select_option([\'lbs\', \'kgs\'],');
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Shipping Dimension Units', 'SHIPPING_DIMENSION_UNITS', 'inches', 'In which unit of measurement does your store save length/width/height for your products?', 7, 7, now(), 'zen_cfg_select_option([\'inches\', \'centimeters\'],');

#PROGRESS_FEEDBACK:!TEXT=Altering Order table - may take some time

ALTER TABLE orders ADD shipping_tax_rate decimal(15,4) DEFAULT NULL AFTER order_tax;

#############
#### Updates for the Wholesale Pricing feature
#PROGRESS_FEEDBACK:!TEXT=Altering Customer table - may take some time

ALTER TABLE customers ADD customers_whole tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE customers MODIFY customers_whole tinyint(1) NOT NULL DEFAULT 0;

#PROGRESS_FEEDBACK:!TEXT=Altering Order table - may take some time

ALTER TABLE orders ADD is_wholesale tinyint(1) DEFAULT NULL;

#PROGRESS_FEEDBACK:!TEXT=Altering Product tables - may take some time

ALTER TABLE products ADD products_price_w varchar(150) NOT NULL DEFAULT '0' AFTER products_price;
ALTER TABLE products_attributes ADD options_values_price_w varchar(150) NOT NULL DEFAULT '0' AFTER options_values_price;
ALTER TABLE products_discount_quantity ADD discount_price_w varchar(150) NOT NULL DEFAULT '0' AFTER discount_price;
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Wholesale Pricing', 'WHOLESALE_PRICING_CONFIG', 'false', 'Should <em>Wholesale Pricing</em> be enabled for your site?  Choose <b>false</b> (the default) if you don\'t want that feature enabled. Otherwise, choose <b>Tax Exempt</b> to enable with tax-exemptions for all wholesale customers or <b>Pricing Only</b> to apply tax as usual for wholesale customers.', 1, 23, now(), 'zen_cfg_select_option([\'false\', \'Tax Exempt\', \'Pricing Only\'],');


#PROGRESS_FEEDBACK:!TEXT=Cleaning up old data - may take some time
UPDATE customers SET customers_telephone=REPLACE(customers_telephone, CONCAT('6','x','.','m','e','/'), '__') WHERE customers_telephone LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE customers SET customers_referral=REPLACE(customers_referral, CONCAT('6','x','.','m','e','/'), '__') WHERE customers_referral LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE address_book SET entry_company=REPLACE(entry_company, CONCAT('6','x','.','m','e','/'), '__') WHERE entry_company LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE address_book SET entry_suburb=REPLACE(entry_suburb, CONCAT('6','x','.','m','e','/'), '__') WHERE entry_suburb LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET customers_company=REPLACE(customers_company, CONCAT('6','x','.','m','e','/'), '__') WHERE customers_company LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET customers_suburb=REPLACE(customers_suburb, CONCAT('6','x','.','m','e','/'), '__') WHERE customers_suburb LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET delivery_company=REPLACE(delivery_company, CONCAT('6','x','.','m','e','/'), '__') WHERE delivery_company LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET delivery_suburb=REPLACE(delivery_suburb, CONCAT('6','x','.','m','e','/'), '__') WHERE delivery_suburb LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET billing_company=REPLACE(billing_company, CONCAT('6','x','.','m','e','/'), '__') WHERE billing_company LIKE CONCAT('%','6','x','.','m','e','/','%');
UPDATE orders SET billing_suburb=REPLACE(billing_suburb, CONCAT('6','x','.','m','e','/'), '__') WHERE billing_suburb LIKE CONCAT('%','6','x','.','m','e','/','%');

UPDATE customers SET customers_telephone=REGEX_REPLACE(customers_telephone, '/(6x\.me\/|src=|<script)/', '__', 1, 0, 'i');


#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='2', project_version_minor='0.0-rc2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.8->2.0.0-rc2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='2', project_version_minor='0.0-rc2', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.8->2.0.0-rc2', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
