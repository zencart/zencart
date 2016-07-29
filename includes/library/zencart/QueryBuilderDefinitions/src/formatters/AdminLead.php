<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\formatters;

/**
 * Class AdminLead
 * @package ZenCart\QueryBuilderDefinitions\formatters
 */
class AdminLead extends AbstractFormatter implements FormatterInterface
{

    /**
     *
     */
    public function format()
    {
        $items = $this->itemList;
        $this->formattedResults = $items;
    }
 }
