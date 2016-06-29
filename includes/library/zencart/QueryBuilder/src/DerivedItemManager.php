<?php
/**
 * Class DerivedItemManager
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\QueryBuilder;

/**
 * Class DerivedItemManager
 * @package ZenCart\QueryBuilder
 */
class DerivedItemManager
{
    /**
     * @param $derivedHandlerList
     * @param $resultItem
     * @return mixed
     */
    public function manageDerivedItems($derivedHandlerList, $resultItem, $currentAction = 'list')
    {
        if (count($derivedHandlerList) == 0) {
            return $resultItem;
        }
        foreach ($derivedHandlerList as $derivedItem) {
            if (!$this->checkDerivedItemContext($derivedItem, $currentAction)) {
                return $resultItem;
            }
            $result = $this->getDerivedItemResult($derivedItem, $resultItem);
            $resultItem[$derivedItem ['field']] = $result;
        }
        return $resultItem;
    }

    protected function  checkDerivedItemContext($derivedItem, $currentAction)
    {
        if (!isset($derivedItem['context']) || $derivedItem['context'] == 'common') {
            return true;
        }
        if ($derivedItem['context'] == $currentAction) {
            return true;
        }
        return false;
    }

    /**
     * @param $derivedItem
     * @param $resultItem
     * @return mixed
     */
    protected function getDerivedItemResult($derivedItem, $resultItem)
    {
        switch ($derivedItem) {
            case (is_string($derivedItem['handler'])):
                $result = $this->{$derivedItem['handler']}($resultItem);
            break;
            case ($derivedItem['handler'] instanceof \Closure):
                $result = $derivedItem['handler']($resultItem);
            break;
        }
        return $result;
    }

    /**
     * @param $resultItem
     * @return array|string
     */
    public static function displayPriceBuilder($resultItem)
    {
        $displayPrice = zen_get_products_display_price($resultItem ['products_id']);
        return $displayPrice;
    }

    /**
     * @param $resultItem
     * @return string
     */
    public static function productCpathBuilder($resultItem)
    {
        $productCpath = zen_get_generated_category_path_rev((isset($resultItem ['categories_id']) ? $resultItem ['categories_id'] : $resultItem ['master_categories_id']));
        return $productCpath;
    }
}
