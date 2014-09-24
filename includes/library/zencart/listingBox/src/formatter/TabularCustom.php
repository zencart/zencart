<?php
/**
 * Class TabularCustom
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\formatter;
/**
 * Class TabularCustom
 * @package ZenCart\ListingBox\formatter
 */
class TabularCustom extends AbstractFormatter
{
    public function format()
    {

        $this->diContainer->get('listingBox')->setTemplateVariable('caption', CAPTION_UPCOMING_PRODUCTS);
        $items = $this->diContainer->get('queryBuilder')->getResultItems();
        $outputLayout = $this->diContainer->get('listingBox')->getOutputLayout();
        $header = array();
        foreach ($outputLayout ['columns'] as $field => $parameters) {
            $header [] = array(
                'title' => $parameters ['title'],
                'col_params' => $parameters ['col_params']
            );
        }
        $this->diContainer->get('listingBox')->setTemplateVariable('headers', $header);
        $listBoxContents = array();
        if (count($items) > 0) {
            foreach ($items as $item) {
                $row = array();
                foreach ($outputLayout ['columns'] as $field => $parameters) {
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
        }
        $this->notify('NOTIFY_LISTING_BOX_FORMATTER_TABULAR_FORMAT_END', NULL, $listBoxContents);
        return $listBoxContents;
    }
}
