<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\DbRepositories;

use queryFactory;

/**
 * Native queryFactory-backed accessor for TABLE_CONFIGURATION.
 */
class ConfigurationRepository
{
    protected array $configAsIntArray = ['SECURITY_CODE_LENGTH'];
    protected array $keepAsStringArray = ['PRODUCTS_MANUFACTURERS_STATUS'];

    public function __construct(private queryFactory $db)
    {
    }

    public function loadConfigSettings(): void
    {
        $configs = $this->db->Execute(
            'SELECT configuration_key, configuration_value, configuration_group_id FROM ' . TABLE_CONFIGURATION
        );

        while (!$configs->EOF) {
            $key = strtoupper((string)$configs->fields['configuration_key']);
            $value = $configs->fields['configuration_value'];
            $groupId = (int)$configs->fields['configuration_group_id'];

            $convertToInt = false;
            if (in_array($key, $this->configAsIntArray, true)) {
                $convertToInt = true;
            } elseif (in_array($groupId, [2, 3], true) && !in_array($key, $this->keepAsStringArray, true)) {
                $convertToInt = true;
            }

            if ($convertToInt) {
                $value = (int)$value;
            }

            if (!defined($key)) {
                define($key, $value);
            }

            $configs->MoveNext();
        }
    }

    public function getByKey(string $configurationKey): ?array
    {
        $configurationKey = $this->db->prepare_input($configurationKey);
        $result = $this->db->Execute(
            "SELECT configuration_id, configuration_key, configuration_value FROM " . TABLE_CONFIGURATION .
            " WHERE configuration_key = '" . $configurationKey . "' LIMIT 1"
        );

        if ($result->EOF) {
            return null;
        }

        return $result->fields;
    }

    public function updateValueByKey(string $configurationKey, string $configurationValue): int
    {
        $configurationKey = $this->db->prepare_input($configurationKey);
        $configurationValue = $this->db->prepare_input($configurationValue);

        $this->db->Execute(
            "UPDATE " . TABLE_CONFIGURATION .
            " SET configuration_value = '" . $configurationValue . "'" .
            " WHERE configuration_key = '" . $configurationKey . "'"
        );

        return $this->db->affectedRows();
    }
}
