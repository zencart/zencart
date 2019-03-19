<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace ZenCart\Page;

/**
 * Class BuilderFactory
 * @package ZenCart\Page
 */
class BuilderFactory
{
    public function factory($factoryType, $listingBox, $request, $listingBoxType = null)
    {
        $className = __NAMESPACE__ . '\\' . $factoryType . 'Builder';
        return new $className($listingBox, $request, $listingBoxType);
    }
}
