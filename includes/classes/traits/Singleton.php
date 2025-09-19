<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 23 New in v1.5.7 $
 */

namespace Zencart\Traits;

/**
 * @since ZC v1.5.7
 */
trait Singleton
{
    private static $instances = array();
    protected function __construct() {}
    /**
     * @since ZC v1.5.7
     */
    protected function __clone() {}
    /**
     * @since ZC v1.5.7
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * @since ZC v1.5.7
     */
    public static function getInstance()
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }
}
