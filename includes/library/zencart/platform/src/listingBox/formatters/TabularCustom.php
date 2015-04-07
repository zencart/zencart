<?php
/**
 * Class TabularCustom
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\formatters;
/**
 * Class TabularCustom
 * @package ZenCart\Platform\listingBox\formatters
 */
class TabularCustom extends AbstractFormatter implements FormatterInterface
{
    /**
     *
     */
    public function format()
    {
        $this->tplVars['caption'] = CAPTION_UPCOMING_PRODUCTS;
        $items = $this->itemList;
        if (count($items) == 0) {
            return;
        }
        $listBoxContents = array();
        $header = array();
        foreach ($this->outputLayout['columns'] as $field => $parameters) {
            $header [] = array(
                'title' => $parameters ['title'],
                'col_params' => $parameters ['col_params']
            );
        }
        $this->tplVars['headers'] = $header;
        foreach ($items as $item) {
            $row = array();
            foreach ($this->outputLayout['columns'] as $field => $parameters) {
                $rowEntry = array(
                    'value' => $item [$field],
                    'col_params' => $parameters ['col_params']
                );
                if (isset($parameters ['formatter'])) {
                    $rowEntry = array(
                        'value' => $parameters ['formatter'](array(
                            'item' => $item,
                            'field' => $field
                        )),
                        'col_params' => $parameters ['col_params']
                    );
                }
                $row[] = $rowEntry;
            }
            $listBoxContents [] = $row;
        }
        $this->formattedResults = $listBoxContents;
    }
}
