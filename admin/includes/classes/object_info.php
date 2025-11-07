<?php

/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 17 Modified in v2.2.0 $
 */

if (PHP_VERSION_ID < 80200 && !class_exists('AllowDynamicProperties')) {
    #[\Attribute(\Attribute::TARGET_CLASS)]
    final class AllowDynamicProperties {}
}
/**
 * Class objectInfo
 * @since ZC v1.0.3
 */
#[AllowDynamicProperties]
class objectInfo
{
    /**
     * @param $object_array
     */
    public function __construct($object_array)
    {
        $this->updateObjectInfo($object_array);
    }

    /**
     * @param $object_array array
     * @since ZC v1.0.3
     */
    public function objectInfo($object_array)
    {
        if (!is_array($object_array)) return;

        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
        $this->object_array = $object_array;
    }

    /**
     * @param $object_array array
     * @since ZC v1.5.5
     */
    public function updateObjectInfo($object_array)
    {
        if (!is_array($object_array)) return;

        foreach ($object_array as $key => $value) {
            $this->$key = zen_db_prepare_input($value);
        }
    }

    /**
     * @since ZC v1.5.6
     */
    public function __isset($field)
    {
        return isset($this->$field);
    }

    /**
     * @since ZC v1.5.6
     */
    public function __set($field, $value)
    {
        $this->$field = $value;
    }

    /**
     * @param $field
     * @return array|string
     * @since ZC v1.5.6
     */
    public function __get($field)
    {
        if (isset($this->$field)) return $this->$field;

        if ($field == 'keys') return array();

        return null;
    }
}
