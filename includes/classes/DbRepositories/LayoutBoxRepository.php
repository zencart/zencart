<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

class LayoutBoxRepository
{
    public function __construct(private queryFactory $db)
    {
    }

    public function getActiveForLocation(int $location, string $template, int $limit = 100): array
    {
        return $this->fetchAll(
            "SELECT * FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_box_location = " . (int)$location .
            " AND layout_box_status = 1" .
            " AND layout_template = '" . $this->db->prepare_input($template) . "'" .
            " ORDER BY layout_box_sort_order LIMIT " . (int)$limit
        );
    }

    public function findFirstByTemplateAndBoxName(string $template, string $boxName): ?array
    {
        $result = $this->db->Execute(
            "SELECT * FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_template = '" . $this->db->prepare_input($template) . "'" .
            " AND layout_box_name = '" . $this->db->prepare_input($boxName) . "'" .
            " LIMIT 1"
        );

        if ($result->EOF) {
            return null;
        }

        return $result->fields;
    }

    public function insert(array $insertValues): int
    {
        $this->db->perform(TABLE_LAYOUT_BOXES, $this->buildSqlDataArray($insertValues));
        return (int)$this->db->insert_ID();
    }

    public function updateByLayoutId(int $layoutId, array $values): void
    {
        $this->db->perform(
            TABLE_LAYOUT_BOXES,
            $this->buildSqlDataArray($values),
            'UPDATE',
            "layout_id = " . (int)$layoutId
        );
    }

    public function deleteByLayoutIdAndName(int $layoutId, string $boxName): void
    {
        $this->db->Execute(
            "DELETE FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_id = " . (int)$layoutId .
            " AND layout_box_name = '" . $this->db->prepare_input($boxName) . "'"
        );
    }

    public function getByTemplate(string $template): array
    {
        return $this->fetchAll(
            "SELECT * FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_template = '" . $this->db->prepare_input($template) . "'"
        );
    }

    public function updateByTemplateAndBoxName(string $template, string $boxName, array $values): void
    {
        $this->db->perform(
            TABLE_LAYOUT_BOXES,
            $this->buildSqlDataArray($values),
            'UPDATE',
            "layout_template = '" . $this->db->prepare_input($template) . "'" .
            " AND layout_box_name = '" . $this->db->prepare_input($boxName) . "'"
        );
    }

    public function getNonHeaderFooterByTemplate(string $template): array
    {
        return $this->fetchAll(
            "SELECT * FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_template = '" . $this->db->prepare_input($template) . "'" .
            " AND layout_box_name NOT LIKE '%ezpages_bar'" .
            " AND layout_box_name NOT LIKE '%\\_header.php'" .
            " AND layout_box_name NOT LIKE '%\\_footer.php'" .
            " ORDER BY layout_box_sort_order, layout_box_sort_order_single, layout_box_name"
        );
    }

    public function getByTemplateAndNameLike(string $template, string $pattern): array
    {
        return $this->fetchAll(
            "SELECT * FROM " . TABLE_LAYOUT_BOXES .
            " WHERE layout_template = '" . $this->db->prepare_input($template) . "'" .
            " AND layout_box_name LIKE '" . $this->db->prepare_input($pattern) . "'" .
            " ORDER BY layout_box_sort_order_single, layout_box_name"
        );
    }

    public function updatePluginDetailsByPrefix(string $pluginKey, string $version): void
    {
        $this->db->Execute(
            "UPDATE " . TABLE_LAYOUT_BOXES .
            " SET plugin_details = '" . $this->db->prepare_input($pluginKey . '/' . $version) . "'" .
            " WHERE plugin_details LIKE '" . $this->db->prepare_input($pluginKey . '/%') . "'"
        );
    }

    public function deleteByPluginDetailsPrefix(string $pluginKey): void
    {
        $this->db->Execute(
            "DELETE FROM " . TABLE_LAYOUT_BOXES .
            " WHERE plugin_details LIKE '" . $this->db->prepare_input($pluginKey . '/%') . "'"
        );
    }

    protected function fetchAll(string $sql): array
    {
        $result = $this->db->Execute($sql);
        $rows = [];
        while (!$result->EOF) {
            $rows[] = $result->fields;
            $result->MoveNext();
        }
        return $rows;
    }

    protected function buildSqlDataArray(array $values): array
    {
        $sqlDataArray = [];
        foreach ($values as $field => $value) {
            $type = 'string';
            if (is_int($value)) {
                $type = 'integer';
            } elseif (is_bool($value)) {
                $type = 'integer';
                $value = (int)$value;
            }

            $sqlDataArray[] = [
                'fieldName' => $field,
                'value' => $value,
                'type' => $type,
            ];
        }

        return $sqlDataArray;
    }
}
