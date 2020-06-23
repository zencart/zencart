#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.6 to v1.5.7
# *
# * @access private
# * @copyright Copyright 2003-2020 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: DrByte 2020 Jun 23 Modified in v1.5.7 $
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

# Improved descriptions for meta tag options
UPDATE configuration SET configuration_title = 'Product page generated &lt;title&gt; tag - include Product Model?', configuration_description = 'When custom Keywords and Description meta tags are not set, include the Product Model in the generated page &lt;title&gt; tag?<br><br>0=no / 1=yes' WHERE configuration_key = 'META_TAG_INCLUDE_MODEL';
UPDATE configuration SET configuration_title = 'Product page generated &lt;title&gt; tag - include Product Price?', configuration_description = 'When custom Keywords and Description meta tags are not set, include the Product Price in the generated page &lt;title&gt; tag?<br><br>0=no / 1=yes' WHERE configuration_key = 'META_TAG_INCLUDE_PRICE';
UPDATE configuration SET configuration_title = 'Product page generated &lt;meta - description&gt; tag - Maximum Length', configuration_description = 'When custom Keywords and Description meta tags are not set, limit the generated &lt;meta - description&gt; tag to this number of words. Default 50.' WHERE configuration_key = 'MAX_META_TAG_DESCRIPTION_LENGTH';
# Other name/description improvements
UPDATE configuration SET configuration_title= 'Order History Box', configuration_description= 'Number of products to display in the order history box' WHERE configuration_key = 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX';
UPDATE configuration SET configuration_description = 'Number of products to display in the \'Also Purchased\' box' WHERE configuration_key = 'MAX_DISPLAY_ALSO_PURCHASED';
UPDATE configuration SET configuration_description = 'Minimum number of products to display in the \'Also Purchased\' box' WHERE configuration_key = 'MIN_DISPLAY_ALSO_PURCHASED';
UPDATE configuration SET configuration_description = 'Maximum number of PayPal IPN Listings in Admin<br />Default is 20' WHERE configuration_key = 'MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN';
UPDATE configuration SET configuration_description = 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone - Can be overridden by correctly written Shipping Module' WHERE configuration_key = 'STORE_SHIPPING_TAX_BASIS';
UPDATE configuration SET configuration_description = 'Check to see if sufficient stock is available' WHERE configuration_key = 'STOCK_CHECK';
UPDATE configuration SET configuration_description = 'Give a WARNING some time before you put your website Down for Maintenance<br />(true=on false=off)<br />If you set the \'Down For Maintenance: ON/OFF\' to true this will automatically be updated to false' WHERE configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE';
#
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Name', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Title Additional Text', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Title Additional text in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Model', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Model in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_INFO_METATAGS_MODEL_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Price', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Price in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_INFO_METATAGS_PRICE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use SITE_TAGLINE', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the defined constant "SITE_TAGLINE" in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS';

UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Name', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRODUCTS_NAME_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Title Additional Text', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Model', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_MODEL_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Price', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_PRICE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use SITE_TAGLINE', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_MUSIC_INFO_METATAGS_TITLE_TAGLINE_STATUS';

UPDATE product_type_layout SET configuration_title = 'Document page &lt;title&gt; tag - default: use Document Title', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Title in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Document page &lt;title&gt; tag - default: use Document Name', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_PRODUCTS_NAME_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Tagline', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Tagline in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_GENERAL_INFO_METATAGS_TITLE_TAGLINE_STATUS';

UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Title', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Title in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Name', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRODUCTS_NAME_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Model', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Model in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_MODEL_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Price', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Price in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_PRICE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Document Tagline', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Document Tagline in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_DOCUMENT_PRODUCT_INFO_METATAGS_TITLE_TAGLINE_STATUS';

UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Name', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Name in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Title Additional Text', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Title Additional text in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRODUCTS_NAME_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Model', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Model in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_MODEL_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use Product Price', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the Product Price in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_PRICE_STATUS';
UPDATE product_type_layout SET configuration_title = 'Product page &lt;title&gt; tag - default: use SITE_TAGLINE', configuration_description = 'Default setting for a new product (can be modified per product).<br>Show the defined constant "SITE_TAGLINE" in the page &lt;title&gt; tag.' WHERE configuration_key = 'SHOW_PRODUCT_FREE_SHIPPING_INFO_METATAGS_TITLE_TAGLINE_STATUS';

# Repair ez-pages table field that was too short in v156
ALTER TABLE ezpages_content MODIFY pages_html_text mediumtext;

# Enable Products to Categories as a menu option
UPDATE admin_pages SET display_on_menu = 'Y' WHERE page_key = 'productsToCategories';
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order) VALUES ('Default Target Category (Products to Multiple Categories Manager)', 'P2C_TARGET_CATEGORY_DEFAULT', '', 'Default Target Category for Products to Multiple Categories Manager (set on page)', 6, 100);

# Rename 'Email Options' to just 'Email'
UPDATE configuration_group set configuration_group_title = 'Email', configuration_group_description = 'Email-related settings' where configuration_group_title = 'E-Mail Options';

# Add NOTIFY_CUSTOMER_DEFAULT
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Default for Notify Customer on Order Status Update?', 'NOTIFY_CUSTOMER_DEFAULT', '1', 'Set the default email behavior on status update to Send Email, Do Not Send Email, or Hide Update.', 1, 120, now(), now(), NULL, 'zen_cfg_select_drop_down(array( array(\'id\'=>\'1\', \'text\'=>\'Email\'), array(\'id\'=>\'0\', \'text\'=>\'No Email\'), array(\'id\'=>\'-1\', \'text\'=>\'Hide\')),');

# Minmax values
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Preview', 'MAX_PREVIEW', '100', '{"error":"TEXT_MAX_PREVIEW","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Preview length<br />100 = Default', 3, 80, now());

# Encrypted Master Password configuration. Using INSERT IGNORE followed by an UPDATE in consideration of shops with EMP already installed.
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer <em>Place Order</em>: Single Admin ID', 'EMP_LOGIN_ADMIN_ID', '0', 'Identify the ID number of an admin that is permitted to use the <em>Place Order</em> feature on the customers list, regardless of their assigned admin-profile. Set the value to 0 to disable the <em>Single Admin ID</em> feature.', 1, 300, now());
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Single Admin ID', configuration_description = 'Identify the ID number of an admin that is permitted to use the <em>Place Order</em> feature on the customers list, regardless of their assigned admin-profile. Set the value to 0 to disable the <em>Single Admin ID</em> feature.' WHERE configuration_key = 'EMP_LOGIN_ADMIN_ID' LIMIT 1;

INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Customer <em>Place Order</em>: Passwordless Login', 'EMP_LOGIN_AUTOMATIC', 'false', 'Login directly to store without entering credentials', 1, 302, now(), 'zen_cfg_select_option(array(\'true\', \'false\'),');
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Passwordless Login', configuration_description = 'Login directly to store without entering credentials' WHERE configuration_key = 'EMP_LOGIN_AUTOMATIC' LIMIT 1;

INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Customer <em>Place Order</em>: Admin Profiles', 'EMP_LOGIN_ADMIN_PROFILE_ID', '0', 'Identify the admin <em>User Profile IDs</em> that are permitted to use the <em>Place Order</em> feature on the customers list &mdash; all admins that are in these profiles are permitted. Enter the value as a comma-separated list (intervening blanks are OK) of Admin Profile IDs, e.g. <b>1, 2, 3</b>. Set the value to 0 to disable the <em>Admin Profiles</em> feature.<br><br><b>Default: 0</b>', 1, 301, now());
UPDATE configuration SET configuration_title = 'Customer <em>Place Order</em>: Admin Profiles', configuration_description = 'Identify the admin <em>User Profile IDs</em> that are permitted to use the <em>Place Order</em> feature on the customers list &mdash; all admins that are in these profiles are permitted. Enter the value as a comma-separated list (intervening blanks are OK) of Admin Profile IDs, e.g. <b>1, 2, 3</b>. Set the value to 0 to disable the <em>Admin Profiles</em> feature.<br><br><b>Default: 0</b>' WHERE configuration_key = 'EMP_LOGIN_ADMIN_PROFILE_ID' LIMIT 1;

#global auth key
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('global auth key', 'GLOBAL_AUTH_KEY', '', '', 6, 30, now(), now(), NULL, NULL);

# New setting, enabling product meta-tags to be conditionally included in search result.
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function) VALUES ('Include meta-tags in product search?', 'ADVANCED_SEARCH_INCLUDE_METATAGS', 'true', 'Should a product\'s meta-tag keywords and meta-tag descriptions be considered in any <code>advanced_search_results</code> displayed?', 1, 18, now(), 'zen_cfg_select_option(array(\'true\', \'false\'),');

# Missed in 1.5.6 upgrade. May already be there so use INSERT IGNORE
INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Admin Usernames', 'ADMIN_NAME_MINIMUM_LENGTH', '4', '{"error":"TEXT_MIN_ADMIN_USER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":4}}}', 'Minimum length of admin usernames (must be 4 or more)', '2', '18', now());
# Country data
UPDATE countries set address_format_id = 5 where countries_iso_code_3 in ('ITA');

# Add language_code
ALTER TABLE orders ADD language_code char(2) NOT NULL default '';

# Add sort_order
ALTER TABLE orders_status ADD sort_order int(11) NOT NULL default 0;

# Improve speed of admin orders page listing
ALTER TABLE orders_total ADD INDEX idx_oid_class_zen (orders_id, class);
ALTER TABLE orders ADD INDEX idx_status_date_id_zen (orders_status, date_purchased, orders_id);

# Add customer secret
ALTER TABLE customers ADD customers_secret varchar(64) NOT NULL default '';

# Add control to enable/disable the display of the 'Ask a Question' block for each product type
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 1, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_MUSIC_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 2, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_DOCUMENT_GENERAL_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 3, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_DOCUMENT_PRODUCT_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 4, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());
INSERT INTO product_type_layout (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, set_function, date_added) VALUES ('Show \"Ask a Question\" button?', 'SHOW_PRODUCT_FREE_SHIPPING_INFO_ASK_A_QUESTION', '1', 'Display the \"Ask a Question\" button on product Info pages? (0 = False, 1 = True)', 5, 14, 'zen_cfg_select_drop_down(array(array(\'id\'=>\'1\', \'text\'=>\'True\'), array(\'id\'=>\'0\', \'text\'=>\'False\')), ', now());

# Add control for enabling product that were disabled and have a products_available_date that has passed in time and is not set to the Zen Cart equivalent of an ignored/empty date.
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable disabled product by available date', 'ENABLE_DISABLED_UPCOMING_PRODUCT', 'Manual', 'How should hidden (disabled) product with a future available date be made visible (active) to customers when the date is reached?<br />', '9', '12', 'zen_cfg_select_option(array(\'Manual\', \'Automatic\'), ', now());

DELETE FROM configuration WHERE configuration_key = 'ADMIN_DEMO';
DELETE FROM configuration WHERE configuration_key = 'UPLOAD_FILENAME_EXTENSIONS';

#val_function update for MIN values
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_FIRST_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_FIRST_NAME_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_LAST_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_LAST_NAME_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_DOB_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_DOB_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_EMAIL_ADDRESS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_EMAIL_ADDRESS_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_STREET_ADDRESS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_STREET_ADDRESS_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_COMPANY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_COMPANY_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_POSTCODE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_POSTCODE_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_CITY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_CITY_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_STATE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_STATE_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_TELEPHONE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_TELEPHONE_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_PASSWORD_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_PASSWORD_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_CC_OWNER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='CC_OWNER_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_CC_NUMBER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='CC_NUMBER_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_CC_CVV_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='CC_CVV_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_REVIEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='REVIEW_TEXT_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_DISPLAY_BESTSELLERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MIN_DISPLAY_BESTSELLERS';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_DISPLAY_ALSO_PURCHASED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MIN_DISPLAY_ALSO_PURCHASED';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_ENTRY_NICK_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='ENTRY_NICK_MIN_LENGTH';
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_USER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":4}}}' WHERE configuration_key ='ADMIN_NAME_MINIMUM_LENGTH';


#val_function update for MAX values
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_ADDRESS_BOOK_ENTRIES_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_ADDRESS_BOOK_ENTRIES';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PAGE_LINKS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PAGE_LINKS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PAGE_LINKS_MOBILE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PAGE_LINKS_MOBILE';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SPECIAL_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SPECIAL_PRODUCTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_NEW_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_NEW_PRODUCTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_UPCOMING_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_UPCOMING_PRODUCTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_MANUFACTURERS_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_MANUFACTURERS_LIST';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_MUSIC_GENRES_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_MUSIC_GENRES_LIST';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RECORD_COMPANY_LIST_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_RECORD_COMPANY_LIST';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RECORD_COMPANY_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_RECORD_COMPANY_NAME_LEN';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_MUSIC_GENRES_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_MUSIC_GENRES_NAME_LEN';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_MANUFACTURERS_NAME_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_MANUFACTURER_NAME_LEN';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_NEW_REVIEWS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_NEW_REVIEWS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_REVIEWS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_RANDOM_SELECT_REVIEWS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_NEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_RANDOM_SELECT_NEW';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_SPECIALS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_RANDOM_SELECT_SPECIALS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_CATEGORIES_PER_ROW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_CATEGORIES_PER_ROW';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_NEW_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_NEW';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_BESTSELLERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_BESTSELLERS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_ALSO_PURCHASED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_ALSO_PURCHASED';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_ORDER_HISTORY_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_ORDER_HISTORY';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_CUSTOMER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_ORDERS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_ORDERS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_RESULTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_REPORTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_RESULTS_CATEGORIES_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_RESULTS_CATEGORIES';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_LISTING_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_LISTING';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_ROW_LISTS_OPTIONS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_ROW_LISTS_OPTIONS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_ROW_LISTS_ATTRIBUTES_CONTROLLER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_ROW_LISTS_ATTRIBUTES_CONTROLLER';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_DOWNLOADS_MANAGER';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_FEATURED';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_FEATURED_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_RANDOM_SELECT_FEATURED_PRODUCTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_RANDOM_SELECT_FEATURED_PRODUCTS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SPECIAL_PRODUCTS_INDEX_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_SHOW_NEW_PRODUCTS_LIMIT_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='SHOW_NEW_PRODUCTS_LIMIT';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_ALL_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_ALL';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_LANGUAGE_FLAGS_COLUMNS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_LANGUAGE_FLAGS_COLUMNS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS';
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_DISPLAY_SEARCH_RESULTS_EZPAGE_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS_EZPAGE';

#val_function update for email addresses
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='STORE_OWNER_EMAIL_ADDRESS';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='EMAIL_FROM';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_ORDER_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_GV_CUSTOMER_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_GV_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='CONTACT_US_LIST';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_LOW_STOCK_EMAILS_TO';


#
# Table structure for table 'count_product_views'
#
CREATE TABLE IF NOT EXISTS count_product_views (
  product_id int(11) NOT NULL default 0,
  language_id int(11) NOT NULL default 1,
  date_viewed date NOT NULL,
  views int(11) default NULL,
  PRIMARY KEY (product_id, language_id, date_viewed),
  KEY idx_pid_lang_date_zen (language_id, product_id, date_viewed),
  KEY idx_date_pid_lang_zen (date_viewed, product_id, language_id)
) ENGINE=MyISAM;


ALTER TABLE admin_profiles MODIFY profile_name varchar(191) NOT NULL default '';
ALTER TABLE admin_profiles ADD UNIQUE idx_profile_name_zen (profile_name);

ALTER TABLE admin_activity_log MODIFY attention MEDIUMTEXT;

# ZC 156 changed these fields in the install but not in the upgrade
ALTER TABLE upgrade_exceptions MODIFY sql_file varchar(128) default NULL;
ALTER TABLE upgrade_exceptions MODIFY reason TEXT;
ALTER TABLE upgrade_exceptions MODIFY errordate datetime default NULL;

# ZC 156 upgrade did these operations, which did not work.
# Adding for people who upgraded to 1.5.6, and are now upgrading again
ALTER TABLE customers_basket DROP final_price;
ALTER TABLE ezpages DROP languages_id;
ALTER TABLE ezpages DROP pages_title;
ALTER TABLE ezpages DROP pages_html_text;

# ZC 155 upgrade missed these operations
ALTER TABLE admin_activity_log ADD logmessage mediumtext NOT NULL;
ALTER TABLE admin_activity_log ADD severity varchar(9) NOT NULL DEFAULT 'info';


# New Plugin tables

# --------------------------------------------------------

#
# Table structure for table 'plugin_control'
#

DROP TABLE IF EXISTS plugin_control;
CREATE TABLE plugin_control (
  unique_key varchar(40) NOT NULL,
  name varchar(64) NOT NULL default '',
  description text,
  type varchar(11) NOT NULL default 'free',
  managed tinyint(1) NOT NULL default 0,
  status tinyint(1) NOT NULL default 0,
  author varchar(64) NOT NULL,
  version varchar(10),
  zc_versions text NOT NULL,
  zc_contrib_id int(11),
  infs tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (unique_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_control_versions'
#

DROP TABLE IF EXISTS plugin_control_versions;
CREATE TABLE plugin_control_versions (
  unique_key varchar(40) NOT NULL,
  version varchar(10),
  author varchar(64) NOT NULL,
  zc_versions text NOT NULL,
  infs tinyint(1) NOT NULL default 0,
  PRIMARY KEY  (unique_key, version)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_groups'
#

DROP TABLE IF EXISTS plugin_groups;
CREATE TABLE plugin_groups (
  unique_key varchar(20) NOT NULL,
  PRIMARY KEY  (unique_key)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'plugin_groups_description'
#

DROP TABLE IF EXISTS plugin_groups_description;
CREATE TABLE plugin_groups_description (
  plugin_group_unique_key varchar(20) NOT NULL,
  language_id int(11) NOT NULL default 1,
  name varchar(64) NOT NULL default '',
  PRIMARY KEY  (plugin_group_unique_key,language_id)
) ENGINE=MyISAM;

INSERT IGNORE INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order) VALUES  ('plugins', 'BOX_MODULES_PLUGINS', 'FILENAME_PLUGIN_MANAGER', '', 'modules', 'Y', 4);


######
# Carry forward from v1.5.6 for early-adopters
UPDATE configuration SET date_added='0001-01-01' where date_added < '0001-01-01';


#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.7', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.6->1.5.7', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.7', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.6->1.5.7', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
