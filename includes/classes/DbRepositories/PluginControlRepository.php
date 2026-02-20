<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

class PluginControlRepository
{
    public function __construct(private queryFactory $db)
    {
    }

    public function getInstalledPlugins(int $status): array
    {
        $results = $this->db->Execute(
            "SELECT * FROM " . TABLE_PLUGIN_CONTROL .
            " WHERE status = " . (int)$status .
            " ORDER BY name, unique_key"
        );

        $pluginList = [];
        while (!$results->EOF) {
            $row = $this->normalizeRow($results->fields);
            $pluginList[$row['unique_key']] = $row;
            $results->MoveNext();
        }

        return $pluginList;
    }

    public function getAll(): array
    {
        $results = $this->db->Execute(
            "SELECT * FROM " . TABLE_PLUGIN_CONTROL
        );

        $pluginList = [];
        while (!$results->EOF) {
            $row = $this->normalizeRow($results->fields);
            $pluginList[$row['unique_key']] = $row;
            $results->MoveNext();
        }

        return $pluginList;
    }

    public function setAllInfs(int $infs): void
    {
        $this->db->Execute(
            "UPDATE " . TABLE_PLUGIN_CONTROL . " SET infs = " . (int)$infs
        );
    }

    public function upsertMany(array $rows): void
    {
        foreach ($rows as $row) {
            $this->db->Execute(
                "INSERT INTO " . TABLE_PLUGIN_CONTROL . " (" .
                "unique_key, name, description, type, status, author, version, zc_versions, infs, zc_contrib_id" .
                ") VALUES (" .
                "'" . $this->db->prepare_input((string)$row['unique_key']) . "', " .
                "'" . $this->db->prepare_input((string)$row['name']) . "', " .
                "'" . $this->db->prepare_input((string)$row['description']) . "', " .
                "'" . $this->db->prepare_input((string)$row['type']) . "', " .
                (int)$row['status'] . ", " .
                "'" . $this->db->prepare_input((string)$row['author']) . "', " .
                "'" . $this->db->prepare_input((string)$row['version']) . "', " .
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

    public function deleteByInfs(int $infs): void
    {
        $this->db->Execute(
            "DELETE FROM " . TABLE_PLUGIN_CONTROL . " WHERE infs = " . (int)$infs
        );
    }

    protected function normalizeRow(array $row): array
    {
        $row['status'] = isset($row['status']) ? (int)$row['status'] : 0;
        $row['managed'] = isset($row['managed']) ? (bool)$row['managed'] : false;
        $row['zc_contrib_id'] = isset($row['zc_contrib_id']) ? (int)$row['zc_contrib_id'] : 0;
        $row['infs'] = isset($row['infs']) ? (int)$row['infs'] : 0;
        return $row;
    }
}
