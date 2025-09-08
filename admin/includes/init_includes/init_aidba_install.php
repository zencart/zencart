<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$db->Execute("INSERT IGNORE INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Additional Images Approach', 'ADDITIONAL_IMAGES_APPROACH', 'legacy', 'Use Legacy mode for old-school directory scanning and matching filenames. Additional images must be uploaded manually via FTP or using Image Handler.<br>Use Modern mode to add additional images directly from the admin product page. Filenames and extensions do not need to match.<br><strong>NOTE:</strong> if you are switching from Legacy to Modern for the first time, you can use the converter tool found in your Tools menu to update all existing product data with existing additional images. Switching back and forth is not recommended.', '4', '100', 'zen_cfg_select_option([\'legacy\', \'modern\'], ', NOW())");

if(!$sniffer->table_exists(TABLE_PRODUCTS_ADDITIONAL_IMAGES) && defined('ADDITIONAL_IMAGES_APPROACH') && ADDITIONAL_IMAGES_APPROACH == 'modern') {
    // create products_additional_images table
    $db->Execute("CREATE TABLE " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            products_id INT(11) NOT NULL,
            additional_image VARCHAR(255) NOT NULL,
            sort_order INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_products_id (products_id),
            UNIQUE KEY idx_pid_img_zen (products_id, additional_image)
            ) ENGINE=MyISAM;");

}
