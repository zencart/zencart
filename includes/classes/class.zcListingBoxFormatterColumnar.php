<?php
/**
 * zcListingBoxFormatterColumnar
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxFormatterColumnar
 *
 * @package classes
 */
class zcListingBoxFormatterColumnar extends base
{
  /**
   *
   * @param zcAbstractListingBoxBase $listingbox
   * @return array
   */
  public function format(zcAbstractListingBoxBase $listingbox)
  {
    $items = $listingbox->getItems();
    $columnCount = $listingbox->getColumnCount();

    $row = 0;
    $col = 0;
    if (count($items) > 0) {
      if (count($items) < $columnCount || $columnCount == 0) {
        $col_width = floor(100 / count($items));
      } else {
        $col_width = floor(100 / $columnCount);
      }
      foreach ( $items as $item ) {
        $item ['colWidth'] = $col_width;
        $item ['useImage'] = ($item ['products_image'] != '' || PRODUCTS_IMAGE_NO_IMAGE_STATUS != 0) ? TRUE : FALSE;
        $listBoxContents [$row] [$col] = $item;
        $col ++;
        if ($col > ($columnCount - 1)) {
          $col = 0;
          $row ++;
        }
      }
    }
    $this->notify('NOTIFY_LISTING_BOX_FORMATTER_COLUMNAR_FORMAT_END', NULL, $listBoxContents);
    return $listBoxContents;
  }
  public function getDefaultTemplate()
  {
    global $template;
    return ($template->get_template_dir('tpl_listingbox_columnar_default.php', DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/tpl_listingbox_columnar_default.php');
  }
}
