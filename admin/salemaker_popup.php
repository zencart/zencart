<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jan 20 Modified in v1.5.6b $
 */
//
require("includes/application_top.php");

require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_SALEMAKER_POPUP . '.php');
$cname = zen_get_category_name($_GET['cid'], (int)$_SESSION['languages_id']);
$deduction_type_array = array(
  array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet"href="includes/stylesheet.css">
  </head>
  <body>
    <h1 class="text-center"><?php echo HEADING_TITLE . ' - ' . $cname; ?></h1>
    <?php echo zen_draw_separator(); ?>
    <table class="table table-striped">
      <thead>
      <tr class="dataTableHeadingRow">
        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SALE_NAME; ?></th>
        <th colspan="2" class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_SALE_DEDUCTION; ?></th>
        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_SALE_DATE_START; ?></th>
        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_SALE_DATE_END; ?></th>
        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
      </tr>
      </thead>
      <tbody>
      <?php
//print_r($_GET);
      $salemaker_sales_query_raw = "SELECT sale_id, sale_status, sale_name, sale_categories_all, sale_deduction_value, sale_deduction_type, sale_pricerange_from,
                                           sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_date_start, sale_date_end, sale_date_added,
                                           sale_date_last_modified, sale_date_status_change
                                    FROM " . TABLE_SALEMAKER_SALES . "
                                    ORDER BY sale_name";
      $salemaker_sales = $db->Execute($salemaker_sales_query_raw);
      foreach ($salemaker_sales as $salemaker_sale) {
        $categories = explode(',', $salemaker_sale['sale_categories_all']);
        foreach ($categories as $key => $value) {
          if ($value == $_GET['cid']) {
            ?>
            <tr>
              <td  class="dataTableContent"><?php echo $salemaker_sale['sale_name']; ?></td>
              <td  class="dataTableContent text-right"><?php echo $salemaker_sale['sale_deduction_value']; ?></td>
              <td  class="dataTableContent"><?php echo $deduction_type_array[$salemaker_sale['sale_deduction_type']]['text']; ?></td>
              <td  class="dataTableContent text-center"><?php echo (($salemaker_sale['sale_date_start'] == '0001-01-01') ? TEXT_SALEMAKER_IMMEDIATELY : zen_date_short($salemaker_sale['sale_date_start'])); ?></td>
              <td  class="dataTableContent text-center"><?php echo (($salemaker_sale['sale_date_end'] == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($salemaker_sale['sale_date_end'])); ?></td>
              <td  class="dataTableContent text-center">
                  <?php
                  if ($salemaker_sale['sale_status'] == '1') {
                    echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10);
                  } else {
                    echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                  }
                  ?>
              </td>
            </tr>
            <?php
          }
        }
      }
      ?>
      </tbody>
    </table>
    <p class="main text-center"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
  </body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');