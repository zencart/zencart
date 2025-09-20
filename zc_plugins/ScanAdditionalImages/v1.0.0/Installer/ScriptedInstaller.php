<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['toolsScanForImages']);
        zen_register_admin_page('toolsScanForImages', 'BOX_TOOLS_SCAN_FOR_IMAGES', 'FILENAME_SCAN_FOR_ADDITIONAL_IMAGES', '', 'tools', 'Y', 20);

        // THE FOLLOWING SHOULD ALREADY BE PART OF CORE ZEN CART, SO THIS IS DUPLICATION
        $fields = [
            'configuration_title' => 'Additional Images Handling',
            'configuration_value' => 'Database',
            'configuration_description' => 'Product Images can be handled in two ways: &quot;Database&quot; or &quot;Filename-Matching&quot;.<br> Use &quot;Database&quot; to allow additional images (any filename/filetype) to be added via the Admin Product Edit page.<br> Use &quot;Filename-Matching&quot; to autodetect additional images based on filename matching (legacy method) where we scan your images directory for files with names that <a href="https://docs.zen-cart.com/user/images/additional_images/" target="_blank">match the primary image filename plus suffixes</a>. This requires manually uploading images to your server via FTP or other methods, but avoids needing to assign images to products via the Admin page. <br> NOTE: a &quot;Scan Product Images To Database&quot; tool is available for installation via the Plugins module and then accessible via the Tools menu.<br>The scanner creates database entries for all additional images that match legacy naming conventions, subsequently allowing all image management from the Product Edit page. The scanner does not modify the images, and can be run periodically to sync new images to the database as needed.',
            'configuration_group_id' => 4,
            'sort_order' => 26,
            'set_function' => 'zen_cfg_select_option([\'Database\', \'Filename-Matching\'], ',
        ];
        $this->addConfigurationKey('ADDITIONAL_IMAGES_HANDLING', $fields);

        $sql = "UPDATE " . TABLE_CONFIGURATION . " SET sort_order = 25 WHERE configuration_key = 'IMAGES_AUTO_ADDED'";
        $this->executeInstallerSql($sql);
        $sql = "UPDATE " . TABLE_CONFIGURATION . " SET sort_order = 27 WHERE configuration_key = 'ADDITIONAL_IMAGES_MODE'";
        $this->executeInstallerSql($sql);
        $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_title = 'Additional Images filename matching pattern', configuration_description = 'In Filename-Matching mode, you can use an &quot;_&quot; suffix in two formats:<br>&quot;strict&quot; = always use &quot;_&quot; suffix<br>&quot;legacy&quot; = only use &quot;_&quot; suffix in subdirectories<br>(Before v210 legacy was the default)<br>Default = strict' WHERE configuration_key = 'ADDITIONAL_IMAGES_MODE'";
        $this->executeInstallerSql($sql);


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
        zen_deregister_admin_pages(['toolsScanForImages']);

        // also clean up using old name for the tool
        zen_deregister_admin_pages(['toolsAidba']);
    }
}
