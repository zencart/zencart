<?php

declare(strict_types=1);

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    // Name of the unique index on (products_id, additional_image). Matches the name core uses
    // in zc_install/sql/install/mysql_zencart.sql so plugin-created and core-created tables agree.
    public const NEW_INDEX_NAME = 'idx_pid_img_zen';

    // Indexes created by earlier builds of this plugin, superseded by NEW_INDEX_NAME:
    // 'idx_products_id' (non-unique, v1.0.0) and 'idx_pid_img' (unique, pre-release v1.0.1).
    protected const OLD_INDEX_NAMES = ['idx_products_id', 'idx_pid_img'];

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

        // create products_additional_images table (no-op on cores that already ship it; definition matches core's)
        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            products_id INT NOT NULL,
            additional_image VARCHAR(191) NOT NULL,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY " . self::NEW_INDEX_NAME . " (products_id, additional_image)
        ) ENGINE=MyISAM";
        $ok = $this->executeInstallerSql($sql);

        $ok = $this->migrateTable() && $ok;

        return parent::executeInstall() && $ok;
    }

    protected function executeUpgrade($oldVersion)
    {
        $ok = $this->migrateTable();

        return parent::executeUpgrade($oldVersion) && $ok;
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['toolsScanForImages']);

        // also clean up using old name for the tool
        zen_deregister_admin_pages(['toolsAidba']);

        return parent::executeUninstall();
    }

    /**
     * Bring a table created by this plugin's v1.0.0 installer up to core's definition,
     * and remove rows that earlier scans should never have inserted.
     * Every step is skipped when it is already in the desired state, so this is safe to re-run.
     */
    protected function migrateTable(): bool
    {
        global $sniffer;

        if (!$sniffer->table_exists(TABLE_PRODUCTS_ADDITIONAL_IMAGES)) {
            return true;
        }

        $ok = $this->cleanUpMainProductsImages();

        return $this->updateIndexes() && $ok;
    }

    /**
     * Remove any rows where a product's main image was recorded as one of its own additional images.
     */
    protected function cleanUpMainProductsImages(): bool
    {
        $sql = "DELETE t1
            FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " t1
            INNER JOIN " . TABLE_PRODUCTS . " t2 ON t1.additional_image = t2.products_image
            WHERE t1.products_id = t2.products_id";

        return $this->executeInstallerSql($sql);
    }

    /**
     * Replace v1.0.0's non-unique index with the unique (products_id, additional_image) index
     * that core defines, and narrow the column to core's width. Only issues an ALTER when
     * something actually differs, since ALTER on MyISAM rewrites the whole table.
     */
    protected function updateIndexes(): bool
    {
        global $sniffer;

        $table = TABLE_PRODUCTS_ADDITIONAL_IMAGES;
        $clauses = [];

        if ($sniffer->field_type($table, 'additional_image', 'varchar(191)') !== true) {
            $clauses[] = "MODIFY COLUMN additional_image VARCHAR(191) NOT NULL";
        }

        foreach (self::OLD_INDEX_NAMES as $old_index) {
            if ($sniffer->indexExists($table, $old_index)) {
                $clauses[] = "DROP INDEX " . $old_index;
            }
        }

        if (!$sniffer->indexExists($table, self::NEW_INDEX_NAME)) {
            // A table without the unique index may hold duplicate pairs (the v1.0.0 scanner and
            // the core product-edit page both inserted without one), which would make ADD UNIQUE fail.
            // Keep the lowest id of each pair.
            $sql = "DELETE t1
                FROM " . $table . " t1
                INNER JOIN " . $table . " t2
                    ON t1.products_id = t2.products_id
                    AND t1.additional_image = t2.additional_image
                    AND t1.id > t2.id";
            if (!$this->executeInstallerSql($sql)) {
                return false;
            }

            $clauses[] = "ADD UNIQUE INDEX " . self::NEW_INDEX_NAME . " (products_id, additional_image)";
        }

        if ($clauses === []) {
            return true;
        }

        $sql = "ALTER TABLE " . $table . " " . implode(', ', $clauses);

        return $this->executeInstallerSql($sql);
    }
}
