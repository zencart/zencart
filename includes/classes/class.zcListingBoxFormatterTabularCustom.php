<?php
/**
 * zcListingBoxFormatterTabularCustom
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: class.base.php 14535 2009-10-07 22:16:19Z wilt $
 */
/**
 * class zcListingBoxFormatterTabularCustom
 *
 * @package classes
 */
class zcListingBoxFormatterTabularCustom extends base
{
  /**
   *
   * @param zcAbstractListingBoxBase $listingbox
   * @return array
   */
  public function format(zcAbstractListingBoxBase $listingbox)
  {
    $listingbox->setTemplateVariable('caption', CAPTION_UPCOMING_PRODUCTS);
    $items = $listingbox->getItems();
    $outputLayout = $listingbox->getOutputLayout();
    foreach ( $outputLayout ['columns'] as $field => $parameters ) {
      $header [] = array(
          'title' => $parameters ['title'],
          'col_params' => $parameters ['col_params']
      );
    }
    $listingbox->setTemplateVariable('headers', $header);
    $listBoxContents = array();
    foreach ( $items as $item ) {
      $row = array();
      foreach ( $outputLayout ['columns'] as $field => $parameters ) {
        if (isset($parameters ['formatter'])) {
          $row [] = array(
              'value' => $parameters ['formatter'](array(
                  'item' => $item,
                  'field' => $field
              )),
              'col_params' => $parameters ['col_params']
          );
        } else {
          $row [] = array(
              'value' => $item [$field],
              'col_params' => $parameters ['col_params']
          );
        }
      }
      $listBoxContents [] = $row;
    }
    $this->notify('NOTIFY_LISTING_BOX_FORMATTER_TABULAR_FORMAT_END', NULL, $listBoxContents);
    return $listBoxContents;
  }
  public function getDefaultTemplate()
  {
    global $template;
    return ($this->mainTemplate = $template->get_template_dir('tpl_listingbox_tabular_default.php', DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/tpl_listingbox_tabular_default.php');
  }
}
