<?php

/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */

/**
 * Class objectInfo
 */
class objectInfo
{
    /**
     * @var
     */
    protected $key;

    /**
     * @param $object_array
     */
    function __construct($object_array)
    {
        $this->updateObjectInfo($object_array);
    }

    /**
     * @param $object_array
     */
    function updateObjectInfo($object_array)
    {
        if (!is_array($object_array)) return;
        reset($object_array);
        while (list($key, $value) = each($object_array)) {
            $this->$key = zen_db_prepare_input($value);
        }
    }
}
