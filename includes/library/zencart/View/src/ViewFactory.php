<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

/**
 * Class ViewFactory
 * @package ZenCart\View
 */
class ViewFactory
{
    /**
     * @param $factoryType
     * @param string $context
     * @return mixed
     */
    public function factory($factoryType, $context = 'admin')
    {
        $result = NAMESPACE_VIEW . '\\AdminView';
        $fileTest = DIR_CATALOG_LIBRARY . URL_VIEW . $context . '/' . $factoryType . '.php';
        if (file_exists($fileTest)) {
            $result = NAMESPACE_VIEW . '\\' . $context . '\\' . $factoryType;
        }
        return new $result($factoryType, new TplVarManager());
     }
}
