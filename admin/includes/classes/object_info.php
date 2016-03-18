<?php

/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Tue Dec 8 22:18:29 2015 +0000 Modified in v1.5.5 $
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
    function objectInfo($object_array)
    {
        if (!is_array($object_array)) return;
        reset($object_array);
        while (list($key, $value) = each($object_array)) {
            $this->$key = zen_db_prepare_input($value);
        }
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
