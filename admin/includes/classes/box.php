<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Aug 22 Modified in v1.5.8-alpha2 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/*
  Example usage:

  $heading = array();
  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_HEADING_TOOLS,
                     'link'  => zen_href_link(basename($PHP_SELF), ''));

  $contents = array();
  $contents[] = array('text'  => SOME_TEXT);

  $box = new box;
  echo $box->infoBox($heading, $contents);
*/

  class box extends boxTableBlock {
      private
          $heading,
          $contents;
      
    function __construct() {
      $this->heading = array();
      $this->contents = array();
    }

    function infoBox($heading, $contents) {
      $this->table_row_parameters = 'infoBoxHeading';
      $this->table_data_parameters = 'infoBoxHeading';
      $this->heading = $this->tableBlock($heading);

      $this->table_row_parameters = '';
      $this->table_data_parameters = 'infoBoxContent';
      $this->contents = $this->tableBlock($contents);

      return $this->heading . $this->contents;
    }
  }
