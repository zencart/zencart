<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 14/05/16
 * Time: 12:30
 */

namespace ZenCart\Lead;


class BuilderFactory
{
    public function factory($factoryType, $listingBox, $request, $listingBoxType = null)
    {
        $className = __NAMESPACE__ . '\\' . $factoryType . 'Builder';
        return new $className($listingBox, $request, $listingBoxType);
    }
}
