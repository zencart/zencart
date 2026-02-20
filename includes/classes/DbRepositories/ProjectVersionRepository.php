<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

/**
 * Native queryFactory-backed accessor for TABLE_PROJECT_VERSION.
 */
class ProjectVersionRepository
{
    public function __construct(private queryFactory $db)
    {
    }

    public function getByKey(string $projectVersionKey): ?array
    {
        $projectVersionKey = $this->db->prepare_input($projectVersionKey);
        $result = $this->db->Execute(
            "SELECT project_version_major, project_version_minor, project_version_patch1, project_version_patch2," .
            " project_version_patch1_source, project_version_patch2_source" .
            " FROM " . TABLE_PROJECT_VERSION .
            " WHERE project_version_key = '" . $projectVersionKey . "' LIMIT 1"
        );

        if ($result->EOF) {
            return null;
        }

        return $result->fields;
    }
}
