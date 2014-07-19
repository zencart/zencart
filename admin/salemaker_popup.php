<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: salemaker_popup.php 5498 2007-01-01 04:10:07Z ajeh $
 */
//
  require("includes/application_top.php");

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_SALEMAKER_POPUP . '.php');
  $cname = zen_get_category_name($_GET['cid'], (int)$_SESSION['languages_id']);

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
<p class="main"><center><h1><?php echo HEADING_TITLE . ' - ' . $cname; ?><?php echo zen_draw_separator(); ?></h1></center></p>
<table width="90%" align="center">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_SALE_NAME; ?></td>
                <td colspan="2"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SALE_DEDUCTION; ?></td>
                  </tr>
                </table></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SALE_DATE_START; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SALE_DATE_END; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
              </tr>
<?php
//print_r($_GET);
  $salemaker_sales_query_raw = "select sale_id, sale_status, sale_name, sale_categories_all, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " order by sale_name";
  $salemaker_sales = $db->Execute($salemaker_sales_query_raw);
  while (!$salemaker_sales->EOF) {
    $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
	while (list($key,$value) = each($categories)) {
	  if ($value == $_GET['cid']) {
?>
              <tr>
                <td  class="dataTableContent" align="left"><?php echo $salemaker_sales->fields['sale_name']; ?></td>
                <td  class="dataTableContent" align="right"><?php echo $salemaker_sales->fields['sale_deduction_value']; ?></td>
                <td  class="dataTableContent" align="left"><?php echo $deduction_type_array[$salemaker_sales->fields['sale_deduction_type']]['text']; ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($salemaker_sales->fields['sale_date_start'] == '0001-01-01') ? TEXT_SALEMAKER_IMMEDIATELY : zen_date_short($salemaker_sales->fields['sale_date_start'])); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($salemaker_sales->fields['sale_date_end'] == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($salemaker_sales->fields['sale_date_end'])); ?></td>
                <td  class="dataTableContent" align="center">
<?php
      if ($salemaker_sales->fields['sale_status'] == '1') {
        echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10);
      } else {
        echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>


			  </tr>
<?php
     }
	}
    $salemaker_sales->MoveNext();
  }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
</table>
<p align="center" class="main"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
</body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
