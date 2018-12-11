<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: products_expected.php 3295 2006-03-28 07:27:49Z drbyte $
//
require('includes/application_top.php');

$db->Execute("UPDATE " . TABLE_PRODUCTS . "
              SET products_date_available = NULL
              WHERE to_days(now()) > to_days(products_date_available)");
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $products_query_raw = "select pd.products_id, pd.products_name, p.products_date_available from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p where p.products_id = pd.products_id and p.products_date_available != '' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by p.products_date_available DESC";
                $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
                $products = $db->Execute($products_query_raw);
                foreach ($products as $product) {
                  if ((!isset($_GET['pID']) || (isset($_GET['pID']) && ($_GET['pID'] == $product['products_id']))) && !isset($pInfo)) {
                    $pInfo = new objectInfo($product);
                  }

                  if (isset($pInfo) && is_object($pInfo) && ($product['products_id'] == $pInfo->products_id)) {
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_products_category_id($pInfo->products_id) . '&pID=' . $pInfo->products_id . '&product_type=' . zen_get_products_type($pInfo->products_id)) . '\'" role="button">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $product['products_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $product['products_name']; ?></td>
              <td class="dataTableContent text-center"><?php echo zen_date_short($product['products_date_available']); ?></td>
              <td class="dataTableContent text-right"><?php
                  if (isset($pInfo) && is_object($pInfo) && ($product['products_id'] == $pInfo->products_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $product['products_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>&nbsp;</td>
              </tr>
              <?php
            }
            ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            if (isset($pInfo) && is_object($pInfo)) {
              $heading[] = array('text' => '<h4>' . $pInfo->products_name . '</h4>');

              $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCT, 'action=new_product' . '&cPath=' . zen_get_products_category_id($pInfo->products_id) . '&pID=' . $pInfo->products_id . '&product_type=' . zen_get_products_type($pInfo->products_id)) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>');
              $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_EXPECTED . ' ' . zen_date_short($pInfo->products_date_available));
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
        <!-- body_text_eof //-->
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED); ?></td>
            <td class="text-right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
        </table>
      </div>
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
