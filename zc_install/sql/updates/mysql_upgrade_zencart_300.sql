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


#PROGRESS_FEEDBACK:!TEXT=Updating configuration settings...

# Convert trusted configuration input renderers from legacy PHP fragments to JSON metadata.
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_country_list"}' WHERE set_function = 'zen_cfg_pull_down_country_list(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_country_list"}' WHERE set_function = 'zen_cfg_pull_down_country_list(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_country_list_none"}' WHERE set_function = 'zen_cfg_pull_down_country_list_none(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_country_list_none"}' WHERE set_function = 'zen_cfg_pull_down_country_list_none(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_exchange_rate_sources"}' WHERE set_function = 'zen_cfg_pull_down_exchange_rate_sources(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_exchange_rate_sources"}' WHERE set_function = 'zen_cfg_pull_down_exchange_rate_sources(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_htmleditors"}' WHERE set_function = 'zen_cfg_pull_down_htmleditors(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_htmleditors"}' WHERE set_function = 'zen_cfg_pull_down_htmleditors(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_order_statuses"}' WHERE set_function = 'zen_cfg_pull_down_order_statuses(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_order_statuses"}' WHERE set_function = 'zen_cfg_pull_down_order_statuses(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_tax_classes"}' WHERE set_function = 'zen_cfg_pull_down_tax_classes(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_tax_classes"}' WHERE set_function = 'zen_cfg_pull_down_tax_classes(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_zone_classes"}' WHERE set_function = 'zen_cfg_pull_down_zone_classes(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_zone_classes"}' WHERE set_function = 'zen_cfg_pull_down_zone_classes(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_pull_down_zone_list"}' WHERE set_function = 'zen_cfg_pull_down_zone_list(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_pull_down_zone_list"}' WHERE set_function = 'zen_cfg_pull_down_zone_list(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_coupon_id"}' WHERE set_function = 'zen_cfg_select_coupon_id(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_coupon_id"}' WHERE set_function = 'zen_cfg_select_coupon_id(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Blank"},{"id":"1","text":"+"},{"id":"2","text":"-"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Blank\'], [\'id\'=>\'1\', \'text\'=>\'+\'], [\'id\'=>\'2\', \'text\'=>\'-\']], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Blank"},{"id":"1","text":"+"},{"id":"2","text":"-"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Blank\'], [\'id\'=>\'1\', \'text\'=>\'+\'], [\'id\'=>\'2\', \'text\'=>\'-\']], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Button Only"},{"id":"1","text":"Button and Product Image"},{"id":"2","text":"Product Image Only"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Button Only\'],[\'id\'=>\'1\', \'text\'=>\'Button and Product Image\'], [\'id\'=>\'2\', \'text\'=>\'Product Image Only\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Button Only"},{"id":"1","text":"Button and Product Image"},{"id":"2","text":"Product Image Only"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Button Only\'],[\'id\'=>\'1\', \'text\'=>\'Button and Product Image\'], [\'id\'=>\'2\', \'text\'=>\'Product Image Only\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Category Name and Image Always"},{"id":"1","text":"Category Name only"},{"id":"2","text":"Category Name and Image when not blank"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Category Name and Image Always\'], [\'id\'=>\'1\', \'text\'=>\'Category Name only\'], [\'id\'=>\'2\', \'text\'=>\'Category Name and Image when not blank\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Category Name and Image Always"},{"id":"1","text":"Category Name only"},{"id":"2","text":"Category Name and Image when not blank"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Category Name and Image Always\'], [\'id\'=>\'1\', \'text\'=>\'Category Name only\'], [\'id\'=>\'2\', \'text\'=>\'Category Name and Image when not blank\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Non-Compliant"},{"id":"1","text":"On"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Non-Compliant\'], [\'id\'=>\'1\', \'text\'=>\'On\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Non-Compliant"},{"id":"1","text":"On"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Non-Compliant\'], [\'id\'=>\'1\', \'text\'=>\'On\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"Align Left"},{"id":"2","text":"Align Center"},{"id":"3","text":"Align Right"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'Align Left\'], [\'id\'=>\'2\', \'text\'=>\'Align Center\'], [\'id\'=>\'3\', \'text\'=>\'Align Right\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"Align Left"},{"id":"2","text":"Align Center"},{"id":"3","text":"Align Right"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'Align Left\'], [\'id\'=>\'2\', \'text\'=>\'Align Center\'], [\'id\'=>\'3\', \'text\'=>\'Align Right\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"On"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'On\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"On"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'On\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"Top of Page"},{"id":"2","text":"Bottom of Page"},{"id":"3","text":"Both Top & Bottom of Page"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'Top of Page\'], [\'id\'=>\'2\', \'text\'=>\'Bottom of Page\'], [\'id\'=>\'3\', \'text\'=>\'Both Top & Bottom of Page\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Off"},{"id":"1","text":"Top of Page"},{"id":"2","text":"Bottom of Page"},{"id":"3","text":"Both Top & Bottom of Page"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Off\'], [\'id\'=>\'1\', \'text\'=>\'Top of Page\'], [\'id\'=>\'2\', \'text\'=>\'Bottom of Page\'], [\'id\'=>\'3\', \'text\'=>\'Both Top & Bottom of Page\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Product ID"},{"id":"1","text":"Name"},{"id":"2","text":"Product Model"},{"id":"3","text":"Product Price - Name"},{"id":"4","text":"Product Price - Model"},{"id":"5","text":"Product Name - Model"},{"id":"6","text":"Product Sort Order"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Product ID\'], [\'id\'=>\'1\', \'text\'=>\'Name\'], [\'id\'=>\'2\', \'text\'=>\'Product Model\'], [\'id\'=>\'3\', \'text\'=>\'Product Price - Name\'], [\'id\'=>\'4\', \'text\'=>\'Product Price - Model\'], [\'id\'=>\'5\', \'text\'=>\'Product Name - Model\'], [\'id\'=>\'6\', \'text\'=>\'Product Sort Order\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"0","text":"Product ID"},{"id":"1","text":"Name"},{"id":"2","text":"Product Model"},{"id":"3","text":"Product Price - Name"},{"id":"4","text":"Product Price - Model"},{"id":"5","text":"Product Name - Model"},{"id":"6","text":"Product Sort Order"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'0\', \'text\'=>\'Product ID\'], [\'id\'=>\'1\', \'text\'=>\'Name\'], [\'id\'=>\'2\', \'text\'=>\'Product Model\'], [\'id\'=>\'3\', \'text\'=>\'Product Price - Name\'], [\'id\'=>\'4\', \'text\'=>\'Product Price - Model\'], [\'id\'=>\'5\', \'text\'=>\'Product Name - Model\'], [\'id\'=>\'6\', \'text\'=>\'Product Sort Order\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Email"},{"id":"0","text":"No Email"},{"id":"-1","text":"Hide"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Email\'], [\'id\'=>\'0\', \'text\'=>\'No Email\'], [\'id\'=>\'-1\', \'text\'=>\'Hide\']],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Email"},{"id":"0","text":"No Email"},{"id":"-1","text":"Hide"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Email\'], [\'id\'=>\'0\', \'text\'=>\'No Email\'], [\'id\'=>\'-1\', \'text\'=>\'Hide\']],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"True"},{"id":"0","text":"False"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'True\'], [\'id\'=>\'0\', \'text\'=>\'False\']], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"True"},{"id":"0","text":"False"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'True\'], [\'id\'=>\'0\', \'text\'=>\'False\']], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Yes"},{"id":"0","text":"No"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Yes\'], [\'id\'=>\'0\', \'text\'=>\'No\']], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Yes"},{"id":"0","text":"No"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Yes\'], [\'id\'=>\'0\', \'text\'=>\'No\']], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Yes, Always ON"},{"id":"0","text":"No, Always OFF"},{"id":"2","text":"Special"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'], [\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'], [\'id\'=>\'2\', \'text\'=>\'Special\']], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_drop_down","choices":[{"id":"1","text":"Yes, Always ON"},{"id":"0","text":"No, Always OFF"},{"id":"2","text":"Special"}]}' WHERE set_function = 'zen_cfg_select_drop_down([[\'id\'=>\'1\', \'text\'=>\'Yes, Always ON\'], [\'id\'=>\'0\', \'text\'=>\'No, Always OFF\'], [\'id\'=>\'2\', \'text\'=>\'Special\']], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5","6","7","8","9","10","11","12"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5","6","7","8","9","10","11","12"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5","6","7","8","9","10","11"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5","6","7","8","9","10","11"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4","5"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\', \'5\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3","4"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\', \'4\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\', \'3\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","2"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'2\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","7","14","30","60","90","120"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'7\', \'14\', \'30\', \'60\', \'90\', \'120\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1","7","14","30","60","90","120"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\', \'7\', \'14\', \'30\', \'60\', \'90\', \'120\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["0","1"]}' WHERE set_function = 'zen_cfg_select_option([\'0\', \'1\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3","4","5","6","7","8","9","10","11","12"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3","4","5","6","7","8","9","10","11","12"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\', \'9\', \'10\', \'11\', \'12\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3","4","5","6","7","8"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3","4","5","6","7","8"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\', \'4\', \'5\', \'6\', \'7\', \'8\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2","3"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\', \'3\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["1","2"]}' WHERE set_function = 'zen_cfg_select_option([\'1\', \'2\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Database","Filename-Matching"]}' WHERE set_function = 'zen_cfg_select_option([\'Database\', \'Filename-Matching\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Database","Filename-Matching"]}' WHERE set_function = 'zen_cfg_select_option([\'Database\', \'Filename-Matching\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Default","Browser"]}' WHERE set_function = 'zen_cfg_select_option([\'Default\', \'Browser\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Default","Browser"]}' WHERE set_function = 'zen_cfg_select_option([\'Default\', \'Browser\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Manual","Automatic"]}' WHERE set_function = 'zen_cfg_select_option([\'Manual\', \'Automatic\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Manual","Automatic"]}' WHERE set_function = 'zen_cfg_select_option([\'Manual\', \'Automatic\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["No","Yes","Found"]}' WHERE set_function = 'zen_cfg_select_option([\'No\', \'Yes\', \'Found\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["No","Yes","Found"]}' WHERE set_function = 'zen_cfg_select_option([\'No\', \'Yes\', \'Found\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["No","Yes"]}' WHERE set_function = 'zen_cfg_select_option([\'No\', \'Yes\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["No","Yes"]}' WHERE set_function = 'zen_cfg_select_option([\'No\', \'Yes\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["None","Standard","Credit Note"]}' WHERE set_function = 'zen_cfg_select_option([\'None\', \'Standard\', \'Credit Note\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["None","Standard","Credit Note"]}' WHERE set_function = 'zen_cfg_select_option([\'None\', \'Standard\', \'Credit Note\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["None","Standard","Credit Note"]}' WHERE set_function = 'zen_cfg_select_option([\'None\', \'Standard\', \'Credit Note\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["None","Standard","Credit Note"]}' WHERE set_function = 'zen_cfg_select_option([\'None\', \'Standard\', \'Credit Note\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["On","Off","Page Not Found"]}' WHERE set_function = 'zen_cfg_select_option([\'On\', \'Off\', \'Page Not Found\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["On","Off","Page Not Found"]}' WHERE set_function = 'zen_cfg_select_option([\'On\', \'Off\', \'Page Not Found\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["PHP","sendmail","sendmail-f","smtp","smtpauth","Gmail","Qmail"]}' WHERE set_function = 'zen_cfg_select_option([\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Gmail\',\'Qmail\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["PHP","sendmail","sendmail-f","smtp","smtpauth","Gmail","Qmail"]}' WHERE set_function = 'zen_cfg_select_option([\'PHP\', \'sendmail\', \'sendmail-f\', \'smtp\', \'smtpauth\', \'Gmail\',\'Qmail\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Shipping","Billing","Store"]}' WHERE set_function = 'zen_cfg_select_option([\'Shipping\', \'Billing\', \'Store\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Shipping","Billing","Store"]}' WHERE set_function = 'zen_cfg_select_option([\'Shipping\', \'Billing\', \'Store\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Shipping","Billing"]}' WHERE set_function = 'zen_cfg_select_option([\'Shipping\', \'Billing\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Shipping","Billing"]}' WHERE set_function = 'zen_cfg_select_option([\'Shipping\', \'Billing\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["TEXT","HTML"]}' WHERE set_function = 'zen_cfg_select_option([\'TEXT\', \'HTML\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["TEXT","HTML"]}' WHERE set_function = 'zen_cfg_select_option([\'TEXT\', \'HTML\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["True","False"]}' WHERE set_function = 'zen_cfg_select_option([\'True\', \'False\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["True","False"]}' WHERE set_function = 'zen_cfg_select_option([\'True\', \'False\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["True","False"]}' WHERE set_function = 'zen_cfg_select_option([\'True\', \'False\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["True","False"]}' WHERE set_function = 'zen_cfg_select_option([\'True\', \'False\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["Yes","No"]}' WHERE set_function = 'zen_cfg_select_option([\'Yes\', \'No\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["Yes","No"]}' WHERE set_function = 'zen_cfg_select_option([\'Yes\', \'No\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["and","or"]}' WHERE set_function = 'zen_cfg_select_option([\'and\', \'or\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["and","or"]}' WHERE set_function = 'zen_cfg_select_option([\'and\', \'or\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["asc","desc"]}' WHERE set_function = 'zen_cfg_select_option([\'asc\', \'desc\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["asc","desc"]}' WHERE set_function = 'zen_cfg_select_option([\'asc\', \'desc\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["false","Tax Exempt","Pricing Only"]}' WHERE set_function = 'zen_cfg_select_option([\'false\', \'Tax Exempt\', \'Pricing Only\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["false","Tax Exempt","Pricing Only"]}' WHERE set_function = 'zen_cfg_select_option([\'false\', \'Tax Exempt\', \'Pricing Only\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["inches","centimeters"]}' WHERE set_function = 'zen_cfg_select_option([\'inches\', \'centimeters\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["inches","centimeters"]}' WHERE set_function = 'zen_cfg_select_option([\'inches\', \'centimeters\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["lbs","kgs"]}' WHERE set_function = 'zen_cfg_select_option([\'lbs\', \'kgs\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["lbs","kgs"]}' WHERE set_function = 'zen_cfg_select_option([\'lbs\', \'kgs\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["national","international","both"]}' WHERE set_function = 'zen_cfg_select_option([\'national\', \'international\', \'both\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["national","international","both"]}' WHERE set_function = 'zen_cfg_select_option([\'national\', \'international\', \'both\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["products_name","date_expected"]}' WHERE set_function = 'zen_cfg_select_option([\'products_name\', \'date_expected\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["products_name","date_expected"]}' WHERE set_function = 'zen_cfg_select_option([\'products_name\', \'date_expected\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["strict","legacy"]}' WHERE set_function = 'zen_cfg_select_option([\'strict\', \'legacy\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["strict","legacy"]}' WHERE set_function = 'zen_cfg_select_option([\'strict\', \'legacy\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["true","false"]}' WHERE set_function = 'zen_cfg_select_option([\'true\', \'false\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["true","false"]}' WHERE set_function = 'zen_cfg_select_option([\'true\', \'false\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["true","false"]}' WHERE set_function = 'zen_cfg_select_option([\'true\', \'false\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["true","false"]}' WHERE set_function = 'zen_cfg_select_option([\'true\', \'false\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["true"]}' WHERE set_function = 'zen_cfg_select_option([\'true\'],';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["true"]}' WHERE set_function = 'zen_cfg_select_option([\'true\'],';
UPDATE configuration SET set_function = '{"function":"zen_cfg_select_option","choices":["true"]}' WHERE set_function = 'zen_cfg_select_option([\'true\'], ';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_select_option","choices":["true"]}' WHERE set_function = 'zen_cfg_select_option([\'true\'], ';
UPDATE configuration SET set_function = '{"function":"zen_cfg_textarea"}' WHERE set_function = 'zen_cfg_textarea(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_textarea"}' WHERE set_function = 'zen_cfg_textarea(';
UPDATE configuration SET set_function = '{"function":"zen_cfg_textarea_small"}' WHERE set_function = 'zen_cfg_textarea_small(';
UPDATE product_type_layout SET set_function = '{"function":"zen_cfg_textarea_small"}' WHERE set_function = 'zen_cfg_textarea_small(';


# update to new default, only if not customized from the original default of 50.
UPDATE configuration SET configuration_value = '5' WHERE configuration_key = 'REVIEW_TEXT_MIN_LENGTH' AND configuration_value = 50 AND (last_modified IS NULL OR last_modified = date_added);

# Remove configuration, configuration_group and admin_pages entries for "New Listing", "Featured Listing" and "All Listing", and "Gzip Compression".
DELETE FROM configuration WHERE configuration_group_id IN (21, 22, 23, 14);
DELETE FROM configuration_group WHERE configuration_group_id IN (21, 22, 23, 14);
DELETE FROM admin_pages WHERE page_key IN ('configNewListing', 'configFeaturedListing', 'configAllListing', 'configGzipCompression');

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
