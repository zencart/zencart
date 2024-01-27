<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (!empty($action)) {
  switch ($action) {
    case 'insert':
      $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
      $zone_code = zen_db_prepare_input($_POST['zone_code']);
      $zone_name = zen_db_prepare_input($_POST['zone_name']);

      $db->Execute("INSERT INTO " . TABLE_ZONES . " (zone_country_id, zone_code, zone_name)
                    VALUES ('" . (int)$zone_country_id . "',
                            '" . zen_db_input($zone_code) . "',
                            '" . zen_db_input($zone_name) . "')");

      zen_redirect(zen_href_link(FILENAME_ZONES));
      break;
    case 'save':
      $zone_id = zen_db_prepare_input($_GET['cID']);
      $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
      $zone_code = zen_db_prepare_input($_POST['zone_code']);
      $zone_name = zen_db_prepare_input($_POST['zone_name']);

      $db->Execute("UPDATE " . TABLE_ZONES . "
                    SET zone_country_id = " . (int)$zone_country_id . ",
                        zone_code = '" . zen_db_input($zone_code) . "',
                        zone_name = '" . zen_db_input($zone_name) . "'
                    WHERE zone_id = " . (int)$zone_id);

      zen_redirect(zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $zone_id));
      break;
    case 'deleteconfirm':
      $zone_id = zen_db_prepare_input($_POST['cID']);

      $db->Execute("DELETE FROM " . TABLE_ZONES . "
                    WHERE zone_id = " . (int)$zone_id);

      zen_redirect(zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page']));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover" role="listbox">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE_NAME; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ZONE_CODE; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id
                                    from " . TABLE_ZONES . " z,
                                         " . TABLE_COUNTRIES . " c
                                    where z.zone_country_id = c.countries_id
                                    order by c.countries_name, z.zone_name";
                $zones_split = new splitPageResults($_GET['zone_page'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows, 'countries_name', zen_field_length(TABLE_COUNTRIES, 'countries_name'));
                $zones = $db->Execute($zones_query_raw);
                foreach ($zones as $zone) {
                  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $zone['zone_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $cInfo = new objectInfo($zone);
                  }

                  if (isset($cInfo) && is_object($cInfo) && ($zone['zone_id'] == $cInfo->zone_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '\'" role="option" aria-selected="true">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $zone['zone_id']) . '\'" role="option" aria-selected="false">' . "\n";
                  }
                  ?>
              <td class="dataTableContent"><?php echo $zone['countries_name']; ?></td>
              <td class="dataTableContent"><?php echo $zone['zone_name']; ?></td>
              <td class="dataTableContent text-center"><?php echo $zone['zone_code']; ?></td>
              <td class="dataTableContent text-right">
                  <?php
                  if (isset($cInfo) && is_object($cInfo) && ($zone['zone_id'] == $cInfo->zone_id)) {
                    echo zen_icon('caret-right', '', '2x', true);
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $zone['zone_id']) . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '" role="button">' . zen_icon('circle-info', '', '2x', true, false) . '</a>';
                  }
                  ?>
                &nbsp;</td>
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
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_ZONE . '</h4>');

                $contents = array('form' => zen_draw_form('zones', FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&action=insert', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONES_NAME, 'zone_name', 'class="control-label"') . zen_draw_input_field('zone_name', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONES_CODE, 'zone_code', 'class="control-label"') . zen_draw_input_field('zone_code', '', 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY_NAME, 'zone_country_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_for_admin_pulldown(), '', 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_ZONE . '</h4>');

                $contents = array('form' => zen_draw_form('zones', FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id . '&action=save', 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONES_NAME, 'zone_name', 'class="control-label"') . zen_draw_input_field('zone_name', htmlspecialchars($cInfo->zone_name, ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONES_CODE, 'zone_code', 'class="control-label"') . zen_draw_input_field('zone_code', $cInfo->zone_code, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY_NAME, 'zone_country_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_country_id', zen_get_countries_for_admin_pulldown(), $cInfo->countries_id, 'class="form-control"'));
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_ZONE . '</h4>');

                $contents = array('form' => zen_draw_form('zones', FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&action=deleteconfirm') . zen_draw_hidden_field('cID', $cInfo->zone_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $cInfo->zone_name . '</b>');
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($cInfo) && is_object($cInfo)) {
                  $heading[] = array('text' => '<h4>' . $cInfo->zone_name . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&cID=' . $cInfo->zone_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_NAME . '<br>' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name);
                }
                break;
            }

            if (!empty($heading) && !empty($contents)) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>

        <div class="row">
          <table class="table">
            <tr>
              <td><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['zone_page'], TEXT_DISPLAY_NUMBER_OF_ZONES); ?></td>
              <td class="text-right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['zone_page'], '', 'zone_page'); ?></td>
            </tr>
            <?php
            if (empty($action)) {
              ?>
              <tr>
                <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_ZONES, 'zone_page=' . $_GET['zone_page'] . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_ZONE; ?></a></td>
              </tr>
              <?php
            }
            ?>
          </table>
        </div>
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
