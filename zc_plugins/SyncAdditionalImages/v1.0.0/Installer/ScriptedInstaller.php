<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsAidba']);
        zen_register_admin_page('toolsAidba', 'BOX_TOOLS_CONVERT_AIDBA', 'FILENAME_AIDBA', '', 'tools', 'Y', 20);

        // THE FOLLOWING SHOULD ALREADY BE PART OF CORE ZEN CART, SO THIS IS DUPLICATION
        $fields = [
            'configuration_title' => 'Additional Images Approach',
            'configuration_value' => 'legacy',
            'configuration_description' => 'Use Legacy mode for old-school directory scanning and matching filenames. Additional images must be uploaded manually via FTP or using Image Handler.<br>Use Modern mode to add additional images directly from the admin product page. Filenames and extensions do not need to match.<br><strong>NOTE:</strong> if you are switching from Legacy to Modern for the first time, you can use the converter tool found in your Tools menu to update all existing product data with existing additional images. Switching back and forth is not recommended.',
            'configuration_group_id' => 4,
            'sort_order' => 80,
            'set_function' => 'zen_cfg_select_option([\'legacy\', \'modern\'], ',
        ];
        $this->addConfigurationKey('ADDITIONAL_IMAGES_APPROACH', $fields);

//        // alter product table to InnoDB if not already done
//        $sql = "ALTER TABLE " . TABLE_PRODUCTS . " ENGINE=InnoDB";
//        $this->executeInstallerSql($sql);
//
//        // create products_additional_images table
//        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (
//            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
//            products_id INT(11) NOT NULL,
//            additional_image VARCHAR(255) NOT NULL,
//            sort_order INT(11) DEFAULT 0,
//            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//            FOREIGN KEY (products_id) REFERENCES " . TABLE_PRODUCTS . "(products_id) ON DELETE CASCADE
//        ) ENGINE=InnoDB";
//        $this->executeInstallerSql($sql);

        // create products_additional_images table
        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            products_id INT(11) NOT NULL,
            additional_image VARCHAR(255) NOT NULL,
            sort_order INT(11) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_products_id (products_id)
        ) ENGINE=MyISAM";
        $this->executeInstallerSql($sql);
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsAidba']);
    }
}
