<?php
/**
 * Class Columnar
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\formatters;
/**
 * Class Columnar
 * @package ZenCart\Platform\listingBox\formatters
 */
class Columnar extends AbstractFormatter implements FormatterInterface
{
    /**
     *
     */
    public function format()
    {
        $items = $this->itemList;
        $formatterParams = $this->outputLayout['formatter']['params'];
        $columnCount = $formatterParams['columnCount'];
        $row = 0;
        $col = 0;
        $listBoxContents = array();
        if (count($items) == 0) {
            return;
        }
        $col_width = floor(100 / $columnCount);
        if (count($items) < $columnCount || $columnCount == 0) {
            $col_width = floor(100 / count($items));
        }
        foreach ($items as $item) {
            $item ['colWidth'] = $col_width;
            $item ['useImage'] = ($item ['products_image'] != '' || PRODUCTS_IMAGE_NO_IMAGE_STATUS != 0) ? true : false;
            $listBoxContents [$row] [$col] = $item;
            $col++;
            if ($col > ($columnCount - 1)) {
                $col = 0;
                $row++;
            }
        }
        $this->formattedResults = $listBoxContents;
    }
}
