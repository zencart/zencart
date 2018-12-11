<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Fri Nov 16 08:37:04 2018 +0100 Modified in v1.5.6 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
      $tax_zone_id = zen_db_prepare_input($_POST['tax_zone_id']);
      $tax_class_id = zen_db_prepare_input($_POST['tax_class_id']);
      $tax_rate = zen_db_prepare_input((float)$_POST['tax_rate']);
      $tax_description = zen_db_prepare_input($_POST['tax_description']);
      $tax_priority = zen_db_prepare_input((int)$_POST['tax_priority']);

      $db->Execute("INSERT INTO " . TABLE_TAX_RATES . " (tax_zone_id, tax_class_id, tax_rate, tax_description, tax_priority, date_added)
                    VALUES ('" . (int)$tax_zone_id . "',
                            '" . (int)$tax_class_id . "',
                            '" . zen_db_input($tax_rate) . "',
                            '" . zen_db_input($tax_description) . "',
                            '" . zen_db_input($tax_priority) . "',
                            now())");

      $new_taxrate_id = $db->Insert_ID();
      zen_record_admin_activity('Tax Rate added, assigned ID ' . $new_taxrate_id, 'info');
      zen_redirect(zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $new_taxrate_id));
      break;
    case 'save':
      $tax_rates_id = zen_db_prepare_input($_GET['tID']);
      $tax_zone_id = zen_db_prepare_input($_POST['tax_zone_id']);
      $tax_class_id = zen_db_prepare_input($_POST['tax_class_id']);
      $tax_rate = zen_db_prepare_input((float)$_POST['tax_rate']);
      $tax_description = zen_db_prepare_input($_POST['tax_description']);
      $tax_priority = zen_db_prepare_input((int)$_POST['tax_priority']);

      $db->Execute("UPDATE " . TABLE_TAX_RATES . "
                    SET tax_rates_id = " . (int)$tax_rates_id . ",
                        tax_zone_id = " . (int)$tax_zone_id . ",
                        tax_class_id = " . (int)$tax_class_id . ",
                        tax_rate = '" . zen_db_input($tax_rate) . "',
                        tax_description = '" . zen_db_input($tax_description) . "',
                        tax_priority = '" . zen_db_input($tax_priority) . "',
                        last_modified = now()
                    WHERE tax_rates_id = " . (int)$tax_rates_id);

      zen_record_admin_activity('Tax Rate updated for tax-rate-id ' . $tax_rates_id, 'info');
      zen_redirect(zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tax_rates_id));
      break;
    case 'deleteconfirm':
      $tax_rates_id = zen_db_prepare_input($_POST['tID']);

      $db->Execute("DELETE FROM " . TABLE_TAX_RATES . "
                    WHERE tax_rates_id = " . (int)$tax_rates_id);
      zen_record_admin_activity('Tax Rate deleted for tax-rate-id ' . (int)$tax_rates_id, 'notice');
      zen_redirect(zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page']));
      break;
  }
}
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
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE_PRIORITY; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_TITLE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TEXT_INFO_RATE_DESCRIPTION; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $rates_query_raw = "select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified
                                    from " . TABLE_TAX_CLASS . " tc,
                                         " . TABLE_TAX_RATES . " r
                                    left join " . TABLE_GEO_ZONES . " z on r.tax_zone_id = z.geo_zone_id
                                    where r.tax_class_id = tc.tax_class_id";
                $rates_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $rates_query_raw, $rates_query_numrows);
                $rates = $db->Execute($rates_query_raw);
                foreach ($rates as $rate) {
                  if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $rate['tax_rates_id']))) && !isset($trInfo) && (substr($action, 0, 3) != 'new')) {
                    $trInfo = new objectInfo($rate);
                  }

                  if (isset($trInfo) && is_object($trInfo) && ($rate['tax_rates_id'] == $trInfo->tax_rates_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $rate['tax_rates_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $rate['tax_priority']; ?></td>
              <td class="dataTableContent"><?php echo $rate['tax_class_title']; ?></td>
              <td class="dataTableContent"><?php echo $rate['geo_zone_name']; ?></td>
              <td class="dataTableContent"><?php echo zen_display_tax_value($rate['tax_rate']); ?>%</td>
              <td class="dataTableContent"><?php echo $rate['tax_description']; ?></td>
              <td class="dataTableContent text-right"><?php
                  if (isset($trInfo) && is_object($trInfo) && ($rate['tax_rates_id'] == $trInfo->tax_rates_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $rate['tax_rates_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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

            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_TAX_RATE . '</h4>');

                $contents = array('form' => zen_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_TITLE, 'tax_class_id', 'class="control-label"') . zen_tax_classes_pull_down('name="tax_class_id" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_NAME, 'tax_zone_id', 'class="control-label"') . zen_geo_zones_pull_down('name="tax_zone_id" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_TAX_RATE, 'tax_rate', 'class="control-label"') . zen_draw_input_field('tax_rate', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_RATE_DESCRIPTION, 'tax_description', 'class="control-label"') . zen_draw_input_field('tax_description', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_TAX_RATE_PRIORITY, 'tax_priority', 'class="control-label"') . zen_draw_input_field('tax_priority', '', 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</h4>');

                $contents = array('form' => zen_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_CLASS_TITLE, 'tax_class_id', 'class="control-label"') . zen_tax_classes_pull_down('name="tax_class_id" class="form-control"', $trInfo->tax_class_id));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_NAME, 'tax_zone_id', 'class="control-label"') . zen_geo_zones_pull_down('name="tax_zone_id" class="form-control"', $trInfo->geo_zone_id));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_TAX_RATE, 'tax_rate', 'class="control-label"') . zen_draw_input_field('tax_rate', $trInfo->tax_rate, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_RATE_DESCRIPTION, 'tax_description', 'class="control-label"') . zen_draw_input_field('tax_description', htmlspecialchars($trInfo->tax_description, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_TAX_RATE_PRIORITY, 'tax_priority', 'class="control-label"') . zen_draw_input_field('tax_priority', $trInfo->tax_priority, 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => 'h4>' . TEXT_INFO_HEADING_DELETE_TAX_RATE . '</h4>');

                $contents = array('form' => zen_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('tID', $trInfo->tax_rates_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $trInfo->tax_class_title . ' ' . $trInfo->tax_rate . '%</b>');
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (!empty($trInfo) && is_object($trInfo)) {
                  $heading[] = array('text' => '<h4>' . $trInfo->tax_class_title . '</h4>');
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_GEO_ZONES, '', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DEFINE_ZONES . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($trInfo->date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($trInfo->last_modified));
                  $contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . $trInfo->tax_description);
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $rates_split->display_count($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_RATES); ?></td>
            <td class="text-right"><?php echo $rates_split->display_links($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_TAX_RATE; ?></a></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
