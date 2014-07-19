<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_upcoming_products.php 6422 2007-05-31 00:51:40Z ajeh $
 */
?>
<!-- bof: upcoming_products -->
<fieldset>
<legend><?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?></legend>
<table border="0" width="100%" cellspacing="0" cellpadding="2" id="upcomingProductsTable" summary="<?php echo SUMMARY_TABLE_UPCOMING_PRODUCTS; ?>">
<caption><?php echo CAPTION_UPCOMING_PRODUCTS; ?></caption>
  <tr>
    <th scope="col" id="upProductsHeading"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
    <th scope="col" id="upDateHeading"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></th>
  </tr>
<?php
    for($i=0, $row=0; $i < sizeof($expectedItems); $i++, $row++) {
      $rowClass = (($row / 2) == floor($row / 2)) ? "rowEven" : "rowOdd";
      echo '  <tr class="' . $rowClass . '">' . "\n";
      echo '    <td ><a href="' . zen_href_link(zen_get_info_page($expectedItems[$i]['products_id']), 'cPath=' . $productsInCategory[$expectedItems[$i]['products_id']] . '&products_id=' . $expectedItems[$i]['products_id']) . '">' . $expectedItems[$i]['products_name'] . '</a></td>' . "\n";
      echo '    <td align="right" >' . zen_date_short($expectedItems[$i]['date_expected']) . '</td>' . "\n";
      echo '  </tr>' . "\n";
    }
?>
</table>
</fieldset>
<!-- eof: upcoming_products -->
