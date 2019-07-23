#
# * This SQL script upgrades the core Zen Cart database structure from v1.5.6 to v1.5.7
# *
# * @package Installer
# * @access private
# * @copyright Copyright 2003-2019 Zen Cart Development Team
# * @copyright Portions Copyright 2003 osCommerce
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id: DrByte  New in v1.5.7 $
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
# * f. If any errors occur, you will be notified.  Some warnings can be ignored.
# * g. When done, you will be taken to the Finished page.
#
#####################################################

# Clear out active customer sessions. Truncating helps the database clean up behind itself.
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;

# Rename 'Email Options' to just 'Email'
UPDATE configuration_group set configuration_group_title = 'Email', configuration_group_description = 'Email-related settings' where configuration_group_title = 'E-Mail Options';

# Add NOTIFY_CUSTOMER_DEFAULT
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Default for Notify Customer on Order Status Update?', 'NOTIFY_CUSTOMER_DEFAULT', '1', 'Set the default email behavior on status update to Send Email, Do Not Send Email, or Hide Update.', 1, 120, now(), now(), NULL, 'zen_cfg_select_drop_down(array( array(\'id\'=>\'1\', \'text\'=>\'Email\'), array(\'id\'=>\'0\', \'text\'=>\'No Email\'), array(\'id\'=>\'-1\', \'text\'=>\'Hide\')),');

# Minmax values 
INSERT INTO configuration (configuration_title, configuration_key, configuration_value, val_function, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Maximum Preview', 'MAX_PREVIEW', '100', '{"error":"TEXT_MAX_PREVIEW","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}', 'Maximum Preview length<br />100 = Default', 3, 80, now());

# Country data 
UPDATE countries set address_format_id = 5 where countries_iso_code_3 in ('ITA'); 

DELETE FROM configuration WHERE configuration_key = 'ADMIN_DEMO';

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
UPDATE configuration SET val_function = '{"error":"TEXT_MIN_ADMIN_DISPLAY_SEARCH_RESULTS_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_DISPLAY_SEARCH_RESULTS';


#val_function update for MAX values
UPDATE configuration SET val_function = '{"error":"TEXT_MAX_ADMIN_ADDRESS_BOOK_ENTRIES_LENGTH","id":"FILTER_VALIDATE_INT","options":{"options":{"min_range":0}}}' WHERE configuration_key ='MAX_ADDRESS_BOOK_ENTRIES';
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
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_GV_CUSTOMER_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_GV_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='CONTACT_US_LIST';
UPDATE configuration SET val_function = '{"error":"TEXT_EMAIL_ADDRESS_VALIDATE","id":"FILTER_CALLBACK","options":{"options":["configurationValidation","sanitizeEmail"]}}' WHERE configuration_key ='SEND_EXTRA_LOW_STOCK_EMAILS_TO';

#############

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
UPDATE project_version SET project_version_major='1', project_version_minor='5.7-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.6->1.5.7-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Main';
UPDATE project_version SET project_version_major='1', project_version_minor='5.7-alpha', project_version_patch1='', project_version_patch1_source='', project_version_patch2='', project_version_patch2_source='', project_version_comment='Version Update 1.5.6->1.5.7-alpha', project_version_date_applied=now() WHERE project_version_key = 'Zen-Cart Database';

#####  END OF UPGRADE SCRIPT
