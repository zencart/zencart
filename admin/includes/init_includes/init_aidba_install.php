<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if(!$sniffer->table_exists(TABLE_PRODUCTS_ADDITIONAL_IMAGES)) {
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
