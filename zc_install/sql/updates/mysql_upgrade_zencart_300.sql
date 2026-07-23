#
# * This SQL script upgrades the core Zen Cart database structure from v2.2.0 to v3.0.0
# *
# * @access private
# * @copyright Copyright 2003-2026 Zen Cart Development Team
# * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
# * @version $Id:  New in v3.0.0 $
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

#PROGRESS_FEEDBACK:!TEXT=Purging caches ...
# Clear out active customer sessions. Truncating helps the database clean up behind itself.
TRUNCATE TABLE whos_online;
TRUNCATE TABLE db_cache;
DELETE FROM customer_password_reset_tokens WHERE created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1440 MINUTE);
DELETE FROM customers_auth_tokens WHERE created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1440 MINUTE);


#PROGRESS_FEEDBACK:!TEXT=Updating table structures!
DROP TABLE IF EXISTS paypal_testing;
ALTER TABLE admin ADD dashboard_layout TEXT NULL;
ALTER TABLE reviews_description ADD reviews_title VARCHAR(128) NOT NULL DEFAULT '';
ALTER TABLE products_description DROP COLUMN products_viewed;
ALTER TABLE configuration ADD is_template_setting TINYINT NOT NULL DEFAULT 0;


#PROGRESS_FEEDBACK:!TEXT=Updating configuration settings...

# update to new default, only if not customized from the original default of 50.
UPDATE configuration SET configuration_value = '5' WHERE configuration_key = 'REVIEW_TEXT_MIN_LENGTH' AND configuration_value = 50 AND (last_modified IS NULL OR last_modified = date_added);

# Remove configuration, configuration_group and admin_pages entries for "New Listing", "Featured Listing" and "All Listing", and "Gzip Compression".
DELETE FROM configuration WHERE configuration_group_id IN (21, 22, 23, 14);
DELETE FROM configuration_group WHERE configuration_group_id IN (21, 22, 23, 14);
DELETE FROM admin_pages WHERE page_key IN ('configNewListing', 'configFeaturedListing', 'configAllListing', 'configGzipCompression');

# Set is_template_setting for configuration keys documented here: https://github.com/zencart/zencart/blob/master/not_for_release/dev_tools/tplSetting-conversion-audit.md
UPDATE configuration SET is_template_setting = 1 WHERE configuration_key IN ('BEST_SELLERS_TRUNCATE', 'BEST_SELLERS_TRUNCATE_MORE', 'BOX_WIDTH_LEFT', 'BOX_WIDTH_RIGHT', 'BREAD_CRUMBS_SEPARATOR', 'CATEGORIES_COUNT_PREFIX', 'CATEGORIES_COUNT_SUFFIX', 'CATEGORIES_COUNT_ZERO', 'CATEGORIES_SEPARATOR', 'CATEGORIES_TABS_STATUS', 'CATEGORY_ICON_IMAGE_HEIGHT', 'CATEGORY_ICON_IMAGE_WIDTH', 'COLUMN_LEFT_STATUS', 'COLUMN_RIGHT_STATUS', 'COLUMN_WIDTH_LEFT', 'COLUMN_WIDTH_RIGHT', 'CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF', 'CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF', 'CUSTOMERS_AUTHORIZATION_FOOTER_OFF', 'CUSTOMERS_AUTHORIZATION_HEADER_OFF', 'DEFINE_BREADCRUMB_STATUS', 'DEFINE_CHECKOUT_SUCCESS_STATUS', 'DEFINE_CONDITIONS_STATUS', 'DEFINE_CONTACT_US_STATUS', 'DEFINE_DISCOUNT_COUPON_STATUS', 'DEFINE_MAIN_PAGE_STATUS', 'DEFINE_PAGE_2_STATUS', 'DEFINE_PAGE_3_STATUS', 'DEFINE_PAGE_4_STATUS', 'DEFINE_PRIVACY_STATUS', 'DEFINE_SHIPPINGINFO_STATUS', 'DEFINE_SITE_MAP_STATUS', 'DOWN_FOR_MAINTENANCE_COLUMN_LEFT_OFF', 'DOWN_FOR_MAINTENANCE_COLUMN_RIGHT_OFF', 'DOWN_FOR_MAINTENANCE_FOOTER_OFF', 'DOWN_FOR_MAINTENANCE_HEADER_OFF', 'EZPAGES_SEPARATOR_FOOTER', 'EZPAGES_SEPARATOR_HEADER', 'EZPAGES_SHOW_PREV_NEXT_BUTTONS', 'EZPAGES_SHOW_TABLE_CONTENTS', 'EZPAGES_STATUS_FOOTER', 'EZPAGES_STATUS_HEADER', 'EZPAGES_STATUS_SIDEBOX', 'IMAGE_PRODUCT_LISTING_HEIGHT', 'IMAGE_PRODUCT_LISTING_WIDTH', 'IMAGE_SHOPPING_CART_HEIGHT', 'IMAGE_SHOPPING_CART_STATUS', 'IMAGE_SHOPPING_CART_WIDTH', 'IMAGE_USE_CSS_BUTTONS', 'MAX_DISPLAY_ALSO_PURCHASED', 'MAX_DISPLAY_BESTSELLERS', 'MAX_DISPLAY_MANUFACTURER_NAME_LEN', 'MAX_DISPLAY_MUSIC_GENRES_NAME_LEN', 'MAX_DISPLAY_NEW_REVIEWS', 'MAX_DISPLAY_ORDER_HISTORY', 'MAX_DISPLAY_PAGE_LINKS', 'MAX_DISPLAY_PAGE_LINKS_MOBILE', 'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX', 'MAX_DISPLAY_PRODUCTS_LISTING', 'MAX_DISPLAY_RECORD_COMPANY_NAME_LEN', 'MAX_LANGUAGE_FLAGS_COLUMNS', 'MAX_MANUFACTURERS_LIST', 'MAX_PREVIEW', 'MAX_RANDOM_SELECT_FEATURED_PRODUCTS', 'MAX_RANDOM_SELECT_NEW', 'MAX_RANDOM_SELECT_REVIEWS','MAX_RANDOM_SELECT_SPECIALS', 'MEDIUM_IMAGE_HEIGHT', 'MEDIUM_IMAGE_WIDTH', 'MIN_DISPLAY_ALSO_PURCHASED', 'MIN_DISPLAY_BESTSELLERS', 'PREVIOUS_NEXT_IMAGE_HEIGHT', 'PREVIOUS_NEXT_IMAGE_WIDTH', 'PREV_NEXT_BAR_LOCATION', 'PRODUCTS_IMAGE_NO_IMAGE', 'PRODUCTS_IMAGE_NO_IMAGE_STATUS', 'PRODUCTS_LIST_PRICE_WIDTH', 'PRODUCT_INFO_CATEGORIES', 'PRODUCT_INFO_CATEGORIES_IMAGE_STATUS', 'PRODUCT_INFO_PREVIOUS_NEXT', 'PRODUCT_LISTING_COLUMNS_PER_ROW', 'PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 'PRODUCT_LIST_ALPHA_SORTER', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS', 'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS_TOP', 'PRODUCT_LIST_CATEGORY_ROW_STATUS', 'PRODUCT_LIST_DESCRIPTION', 'PRODUCT_LIST_IMAGE', 'PRODUCT_LIST_MANUFACTURER', 'PRODUCT_LIST_MODEL', 'PRODUCT_LIST_NAME', 'PRODUCT_LIST_PRICE', 'PRODUCT_LIST_PRICE_BUY_NOW', 'PRODUCT_LIST_QUANTITY', 'PRODUCT_LIST_WEIGHT', 'SHOW_ACCOUNT_LINKS_ON_SITE_MAP', 'SHOW_BANNERS_GROUP_SET1', 'SHOW_BANNERS_GROUP_SET2', 'SHOW_BANNERS_GROUP_SET3', 'SHOW_BANNERS_GROUP_SET4', 'SHOW_BANNERS_GROUP_SET5', 'SHOW_BANNERS_GROUP_SET6', 'SHOW_BANNERS_GROUP_SET7', 'SHOW_BANNERS_GROUP_SET8', 'SHOW_BANNERS_GROUP_SET_ALL', 'SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES', 'SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS', 'SHOW_CATEGORIES_BOX_PRODUCTS_ALL', 'SHOW_CATEGORIES_BOX_PRODUCTS_NEW', 'SHOW_CATEGORIES_BOX_SPECIALS', 'SHOW_CATEGORIES_SEPARATOR_LINK', 'SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS', 'SHOW_CUSTOMER_GREETING', 'SHOW_FOOTER_IP', 'SHOW_PREVIOUS_NEXT_IMAGES', 'SHOW_PREVIOUS_NEXT_STATUS', 'SHOW_PRODUCTS_SOLD_OUT_IMAGE', 'SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS', 'SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS', 'SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS', 'SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS', 'SHOW_SHIPPING_ESTIMATOR_BUTTON', 'SHOW_SHOPPING_CART_BOX_STATUS', 'SHOW_SHOPPING_CART_DELETE', 'SHOW_SHOPPING_CART_UPDATE', 'SHOW_TOTALS_IN_CART', 'SMALL_IMAGE_HEIGHT', 'SMALL_IMAGE_WIDTH', 'SUBCATEGORY_IMAGE_TOP_HEIGHT', 'SUBCATEGORY_IMAGE_TOP_WIDTH', 'USE_SPLIT_LOGIN_MODE'); 

# Correct name for Bosnia and Herzegovina
UPDATE countries SET countries_name = 'Bosnia and Herzegovina' WHERE countries_iso_code_2 = 'BA' LIMIT 1;

#PROGRESS_FEEDBACK:!TEXT=Finalizing ... Done!

#### VERSION UPDATE STATEMENTS
## THE FOLLOWING 2 SECTIONS SHOULD BE THE "LAST" ITEMS IN THE FILE, so that if the upgrade fails prematurely, the version info is not updated.
##The following updates the version HISTORY to store the prior version info (Essentially "moves" the prior version info from the "project_version" to "project_version_history" table
#NEXT_X_ROWS_AS_ONE_COMMAND:3
INSERT INTO project_version_history (project_version_key, project_version_major, project_version_minor, project_version_patch, project_version_date_applied, project_version_comment)
SELECT project_version_key, project_version_major, project_version_minor, project_version_patch1 as project_version_patch, project_version_date_applied, project_version_comment
FROM project_version;

## Now set to new version
SET @VERSION_MAJOR = '3';
SET @VERSION_MINOR = '0.0-dev';
SET @DB_MAJOR = '2';
SET @DB_MINOR = '9.9';

UPDATE project_version
SET
    project_version_major = @VERSION_MAJOR,
    project_version_minor = @VERSION_MINOR,
    project_version_patch1 = '',
    project_version_patch1_source = '',
    project_version_patch2 = '',
    project_version_patch2_source = '',
    project_version_comment = CONCAT('Version Update to ', @VERSION_MAJOR, '.', @VERSION_MINOR),
    project_version_date_applied = now()
WHERE project_version_key = 'Zen-Cart Main';

UPDATE project_version
SET
    project_version_major = @DB_MAJOR,
    project_version_minor = @DB_MINOR,
    project_version_patch1 = '',
    project_version_patch1_source = '',
    project_version_patch2 = '',
    project_version_patch2_source = '',
    project_version_comment = CONCAT('Version Update to ', @DB_MAJOR, '.', @DB_MINOR),
    project_version_date_applied = now()
WHERE project_version_key = 'Zen-Cart Database';

##### END OF UPGRADE SCRIPT
