<?php

declare(strict_types=1);
/**
 * Class ProductConfigurationSwitch
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.5.8
 */

class ProductConfigurationSwitch
{
    protected array $layout_data = [];
    protected array $configuration_data = [];
    protected string $type_handler;
    protected int $products_type;

    public function __construct($lookup, protected string $prefix = 'SHOW_', protected string $suffix = '_INFO', protected string $field_prefix = '_', protected string $field_suffix = '')
    {
        global $db;

        $sql = "SELECT products_type FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$lookup;
        $type_lookup = $db->Execute($sql, 1);

        if ($type_lookup->RecordCount() === 0) {
            return;
        }

        $this->products_type = (int)$type_lookup->fields['products_type'];

        $sql = "SELECT type_handler FROM " . TABLE_PRODUCT_TYPES . " WHERE type_id = " . (int)$type_lookup->fields['products_type'];
        $show_key = $db->Execute($sql, 1);

        $this->type_handler = $show_key->fields['type_handler'];

        $zv_key = strtoupper($prefix . $this->type_handler . $suffix . $field_prefix . "%" . $field_suffix);

        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_PRODUCT_TYPE_LAYOUT . " WHERE configuration_key LIKE '" . zen_db_input($zv_key) . "'";
        $zv_key_values = $db->Execute($sql);

        foreach ($zv_key_values as $entry) {
            $this->layout_data[$entry['configuration_key']] = $entry['configuration_value'];
        }

        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '" . zen_db_input($zv_key) . "'";
        $zv_key_values = $db->Execute($sql);

        foreach ($zv_key_values as $entry) {
            $this->configuration_data[$entry['configuration_key']] = $entry['configuration_value'];
        }
    }

    /**
     * @since ZC v1.5.8
     */
    public function getSwitch($field): string|false
    {
        $switch = strtoupper($this->prefix . $this->type_handler . $this->suffix . $this->field_prefix . $field . $this->field_suffix);
        return $this->layout_data[$switch] ?? $this->configuration_data[$switch] ?? false;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getProductsType(): int
    {
        return $this->products_type;
    }
}
