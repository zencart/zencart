<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

class PluginControlVersionRepository
{
    public function __construct(private queryFactory $db)
    {
    }

    public function getByUniqueKey(string $uniqueKey): array
    {
        $results = $this->db->Execute(
            "SELECT * FROM " . TABLE_PLUGIN_CONTROL_VERSIONS .
            " WHERE unique_key = '" . $this->db->prepare_input($uniqueKey) . "'"
        );

        $versions = [];
        while (!$results->EOF) {
            $versions[] = $results->fields;
            $results->MoveNext();
        }

        return $versions;
    }

    public function setAllInfs(int $infs): void
    {
        $this->db->Execute(
            "UPDATE " . TABLE_PLUGIN_CONTROL_VERSIONS . " SET infs = " . (int)$infs
        );
    }

    public function upsertMany(array $rows): void
    {
        foreach ($rows as $row) {
            $this->db->Execute(
                "INSERT INTO " . TABLE_PLUGIN_CONTROL_VERSIONS . " (" .
                "unique_key, author, version, zc_versions, infs" .
                ") VALUES (" .
                "'" . $this->db->prepare_input((string)$row['unique_key']) . "', " .
                "'" . $this->db->prepare_input((string)$row['author']) . "', " .
                "'" . $this->db->prepare_input((string)$row['version']) . "', " .
                "'" . $this->db->prepare_input((string)$row['zc_versions']) . "', " .
                (int)$row['infs'] .
                ") ON DUPLICATE KEY UPDATE " .
                "infs = VALUES(infs)"
            );
        }
    }

    public function deleteByInfs(int $infs): void
    {
        $this->db->Execute(
            "DELETE FROM " . TABLE_PLUGIN_CONTROL_VERSIONS . " WHERE infs = " . (int)$infs
        );
    }
}
