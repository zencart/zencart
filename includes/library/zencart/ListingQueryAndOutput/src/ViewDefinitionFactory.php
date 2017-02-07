<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

namespace ZenCart\ListingQueryAndOutput;

/**
 * Class ViewDefinitionFactory
 * @package ZenCart\ListingQueryAndOutput
 */
class ViewDefinitionFactory
{

    /**
     * @param $type
     * @param $request
     * @param $modelFactory
     * @return mixed
     */
    public function factory($type, $request, $modelFactory)
    {
        $definitionClass = NAMESPACE_LISTINGQUERYANDOUTPUT . '\\definitions\\' . $type;
        return new $definitionClass($request, $modelFactory);
    }
}
