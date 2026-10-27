<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 New in v2.2.1 $
 */

namespace Zencart\DbRepositories;

use queryFactory;

/**
 * @since ZC v2.2.0
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class PluginControlRepository
{
    private int $pluginKeyMaxLength;
    private int $pluginNameMaxLength;
    private int $pluginAuthorMaxLength;
    private int $pluginVersionMaxLength;

    public function __construct(private ?queryFactory $db = null)
    {
        if ($this->db === null) {
            global $db;
            $this->db = $db;
        }

        $this->pluginKeyMaxLength = $this->getFieldLength('unique_key');
        $this->pluginNameMaxLength = $this->getFieldLength('name');
        $this->pluginAuthorMaxLength = $this->getFieldLength('author');
        $this->pluginVersionMaxLength = $this->getFieldLength('version');
    }

    /**
     * Note: Replaces zen_field_length since this class is instantiated by application_bootstrap.php
     * prior to the functions' load.
     *
     * @since ZC vv2.3.0
     */
    private function getFieldLength($field_name): int
    {
        $query = $this->db->MetaColumns(TABLE_PLUGIN_CONTROL);
        return (int)$query[strtoupper($field_name)]->max_length;
    }

    /**
     * @since ZC v2.3.0
     */
    public function getPluginKeyMaxLength(): int
    {
        return $this->pluginKeyMaxLength;
    }

    /**
     * @since ZC v2.3.0
     */
    public function getPluginNameMaxLength(): int
    {
        return $this->pluginNameMaxLength;
    }

    /**
     * @since ZC v2.3.0
     */
    public function getPluginAuthorMaxLength(): int
    {
        return $this->pluginAuthorMaxLength;
    }

    /**
     * @since ZC v2.3.0
     */
    public function getPluginVersionMaxLength(): int
    {
        return $this->pluginVersionMaxLength;
    }

    /**
     * @since ZC v2.2.0
     */
    public function getInstalledPlugins(int $status): array
    {
        $results = $this->db->Execute(
            "SELECT * FROM " . TABLE_PLUGIN_CONTROL .
            " WHERE status = " . (int)$status .
            " ORDER BY name, unique_key"
        );

        $pluginList = [];
        foreach ($results as $result) {
            $row = $this->normalizeRow($result);
            $pluginList[$row['unique_key']] = $row;
        }

        return $pluginList;
    }

    /**
     * @since ZC v2.2.0
     */
    public function getAll(): array
    {
        $results = $this->db->Execute(
            "SELECT * FROM " . TABLE_PLUGIN_CONTROL
        );

        $pluginList = [];
        foreach ($results as $result) {
            $row = $this->normalizeRow($result);
            $pluginList[$row['unique_key']] = $row;
        }

        return $pluginList;
    }

    /**
     * @since ZC v2.2.0
     */
    public function setAllInfs(int $infs): void
    {
        /**
         * Method is valid only when run in the Zen Cart admin environment.
         */
        if (IS_ADMIN_FLAG !== true) {
            return;
        }

        $this->db->Execute(
            "UPDATE " . TABLE_PLUGIN_CONTROL . " SET infs = " . (int)$infs
        );
    }

    /**
     * @since ZC v2.2.0
     */
    public function upsertMany(array $rows): void
    {
        /**
         * Method is valid only when run in the Zen Cart admin environment.
         */
        if (IS_ADMIN_FLAG !== true) {
            return;
        }

        foreach ($rows as $row) {
            $message = '';

            $unique_key = (string)$row['unique_key'];
            if (mb_strlen($unique_key) > $this->pluginKeyMaxLength) {
                $message .= "The Plugin Key ($unique_key) is longer than the maximum allowed ({$this->pluginKeyMaxLength}). ";
            }

            $name = (string)$row['name'];
            if (mb_strlen($name) > $this->pluginNameMaxLength) {
                $message .= "The Plugin Name ($name) is longer than the maximum allowed({$this->pluginNameMaxLength}). ";
            }

            $author = (string)$row['author'];
            if (mb_strlen($author) > $this->pluginAuthorMaxLength) {
                $message .= "The Plugin Author ($author) is longer than the maximum allowed ({$this->pluginAuthorMaxLength}). ";
            }

            $version = (string)$row['version'];
            if (mb_strlen($version) > $this->pluginVersionMaxLength) {
                $message .= "The Plugin Version ($version) is longer than the maximum allowed({$this->pluginVersionMaxLength}). ";
            }

            if ($message !== '') {
                $message = "The plugin with a key of $unique_key was not updated. " . $message;
                zen_record_admin_activity($message, 'error');
                continue;
            }

            $this->db->Execute(
                "INSERT INTO " . TABLE_PLUGIN_CONTROL . " (" .
                "unique_key, name, description, type, status, author, version, zc_versions, infs, zc_contrib_id" .
                ") VALUES (" .
                "'" . $this->db->prepare_input($unique_key) . "', " .
                "'" . $this->db->prepare_input($name) . "', " .
                "'" . $this->db->prepare_input((string)$row['description']) . "', " .
                "'" . $this->db->prepare_input((string)$row['type']) . "', " .
                (int)$row['status'] . ", " .
                "'" . $this->db->prepare_input($author) . "', " .
                "'" . $this->db->prepare_input($version) . "', " .
                "'" . $this->db->prepare_input((string)$row['zc_versions']) . "', " .
                (int)$row['infs'] . ", " .
                (int)$row['zc_contrib_id'] .
                ") ON DUPLICATE KEY UPDATE " .
                "name = VALUES(name), " .
                "description = VALUES(description), " .
                "infs = VALUES(infs), " .
                "author = VALUES(author), " .
                "zc_contrib_id = VALUES(zc_contrib_id)"
            );
        }
    }

    /**
     * @since ZC v2.2.0
     */
    public function deleteByInfs(int $infs): void
    {
        /**
         * Method is valid only when run in the Zen Cart admin environment.
         */
        if (IS_ADMIN_FLAG !== true) {
            return;
        }

        $this->db->Execute(
            "DELETE FROM " . TABLE_PLUGIN_CONTROL . " WHERE infs = " . (int)$infs
        );
    }

    /**
     * @since ZC v2.2.0
     */
    protected function normalizeRow(array $row): array
    {
        $row['status'] = isset($row['status']) ? (int)$row['status'] : 0;
        $row['managed'] = isset($row['managed']) ? (bool)$row['managed'] : false;
        $row['zc_contrib_id'] = isset($row['zc_contrib_id']) ? (int)$row['zc_contrib_id'] : 0;
        $row['infs'] = isset($row['infs']) ? (int)$row['infs'] : 0;
        return $row;
    }
}
