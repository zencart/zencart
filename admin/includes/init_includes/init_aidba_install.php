<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if(!defined('ADDITIONAL_IMAGES_APPROACH')) {
    $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Additional Images Approach', 'ADDITIONAL_IMAGES_APPROACH', 'old', 'Enable Additional Images Module', '4', '100', 'zen_cfg_select_option(array(\'old\', \'modern\'), ', NOW())");
}

if(!$sniffer->table_exists(TABLE_PRODUCTS_ADDITIONAL_IMAGES) && defined('ADDITIONAL_IMAGES_APPROACH') && ADDITIONAL_IMAGES_APPROACH == 'modern') {
    // alter product table to InnoDB if not already done
    $db->Execute("ALTER TABLE " . TABLE_PRODUCTS ." ENGINE=InnoDB;");

    // create products_additional_images table
    $db->Execute("CREATE TABLE " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            products_id INT(11) NOT NULL,
            additional_image VARCHAR(255) NOT NULL,
            sort_order INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (products_id) REFERENCES " . TABLE_PRODUCTS ."(products_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    zen_register_admin_page('toolsAidba', 'BOX_TOOLS_CONVERT_AIDBA', 'FILENAME_AIDBA', '', 'tools', 'Y');
}
