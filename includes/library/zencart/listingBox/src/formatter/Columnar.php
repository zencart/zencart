<?php
/**
 * Class Columnar
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\formatter;
/**
 * Class Columnar
 * @package ZenCart\ListingBox\formatter
 */
class Columnar extends AbstractFormatter
{
    public function format()
    {
        $items = $this->diContainer->get('queryBuilder')->getResultItems();
        $columnCount = $this->diContainer->get('listingBox')->getColumnCount();

        $row = 0;
        $col = 0;
        $listBoxContents = array();
        if (count($items) == 0) {
            return array();
        }
        $col_width = floor(100 / $columnCount);
        if (count($items) < $columnCount || $columnCount == 0) {
            $col_width = floor(100 / count($items));
        }
        foreach ($items as $item) {
            $item ['colWidth'] = $col_width;
            $item ['useImage'] = ($item ['products_image'] != '' || PRODUCTS_IMAGE_NO_IMAGE_STATUS != 0) ? TRUE : FALSE;
            $listBoxContents [$row] [$col] = $item;
            $col++;
            if ($col > ($columnCount - 1)) {
                $col = 0;
                $row++;
            }
        }
        $this->notify('NOTIFY_LISTING_BOX_FORMATTER_COLUMNAR_FORMAT_END', NULL, $listBoxContents);
        return $listBoxContents;
    }
}
