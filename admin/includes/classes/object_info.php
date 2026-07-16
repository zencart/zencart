<?php

declare(strict_types=1);

/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 17 Modified in v2.2.0 $
 */

/**
 * Class objectInfo
 * @since ZC v1.0.3
 */
#[AllowDynamicProperties]
class objectInfo
{
    public function __construct(array $object_array)
    {
        $this->updateObjectInfo($object_array);
    }

    /**
     * @since ZC v1.0.3
     */
    public function objectInfo(array $object_array): void
    {
        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
    }

    /**
     * @since ZC v1.5.5
     */
    public function updateObjectInfo(array $object_array): void
    {
        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
    }

    /**
     * @since ZC v1.5.6
     */
    public function __isset(string $field): bool
    {
        return isset($this->$field);
    }

    /**
     * @since ZC v1.5.6
     */
    public function __set(string $field, mixed $value): void
    {
        $this->$field = $value;
    }

    /**
     * @since ZC v1.5.6
     */
    public function __get(string $field): mixed
    {
        if (isset($this->$field)) {
            return $this->$field;
        }

        if ($field === 'keys') {
            return [];
        }

        return null;
    }
}
