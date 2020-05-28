<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$saction = (isset($_GET['saction']) ? $_GET['saction'] : '');
if (isset($_GET['zID'])) {
  $_GET['zID'] = (int)$_GET['zID'];
}
if (isset($_GET['zpage'])) {
  $_GET['zpage'] = (int)$_GET['zpage'];
}
if (isset($_GET['spage'])) {
  $_GET['spage'] = (int)$_GET['spage'];
}

if (zen_not_null($saction)) {
  switch ($saction) {
    case 'insert_sub':
      $zID = zen_db_prepare_input($_GET['zID']);
      $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
      $zone_id = zen_db_prepare_input($_POST['zone_id']);

      $db->Execute("INSERT INTO " . TABLE_ZONES_TO_GEO_ZONES . "(zone_country_id, zone_id, geo_zone_id, date_added)
                    VALUES ('" . (int)$zone_country_id . "',
                            '" . (int)$zone_id . "',
                            '" . (int)$zID . "',
                            now())");

      $new_subzone_id = $db->Insert_ID();

//        zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $new_subzone_id));
      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list' . '&sID=' . $new_subzone_id));
      break;
    case 'save_sub':
      $sID = zen_db_prepare_input($_GET['sID']);
      $zID = zen_db_prepare_input($_GET['zID']);
      $zone_country_id = zen_db_prepare_input($_POST['zone_country_id']);
      $zone_id = zen_db_prepare_input($_POST['zone_id']);

      $db->Execute("UPDATE " . TABLE_ZONES_TO_GEO_ZONES . "
                    SET geo_zone_id = " . (int)$zID . ",
                        zone_country_id = " . (int)$zone_country_id . ",
                        zone_id = " . (zen_not_null($zone_id) ? (int)$zone_id : 'null') . ",
                        last_modified = now()
                    WHERE association_id = " . (int)$sID);


      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $_GET['sID']));
      break;
    case 'deleteconfirm_sub':
      $sID = zen_db_prepare_input($_POST['sID']);

      $db->Execute("DELETE FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                    WHERE association_id = " . (int)$sID);

      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage']));
      break;
  }
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert_zone':
      $geo_zone_name = zen_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = zen_db_prepare_input($_POST['geo_zone_description']);

      $db->Execute("INSERT INTO " . TABLE_GEO_ZONES . " (geo_zone_name, geo_zone_description, date_added)
                    VALUES ('" . zen_db_input($geo_zone_name) . "',
                            '" . zen_db_input($geo_zone_description) . "',
                            now())");

      $new_zone_id = $db->Insert_ID();
      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zID=' . $new_zone_id));
      break;
    case 'save_zone':
      $zID = zen_db_prepare_input($_GET['zID']);
      $geo_zone_name = zen_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = zen_db_prepare_input($_POST['geo_zone_description']);

      $db->Execute("UPDATE " . TABLE_GEO_ZONES . "
                    SET geo_zone_name = '" . zen_db_input($geo_zone_name) . "',
                        geo_zone_description = '" . zen_db_input($geo_zone_description) . "',
                        last_modified = now()
                    WHERE geo_zone_id = " . (int)$zID);

      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zID=' . $_GET['zID']));
      break;
    case 'deleteconfirm_zone':
      $zID = zen_db_prepare_input($_POST['zID']);

      $check_tax_rates = $db->Execute("SELECT tax_zone_id
                                       FROM " . TABLE_TAX_RATES . "
                                       WHERE tax_zone_id = " . (int)$zID);
      if ($check_tax_rates->RecordCount() > 0) {
        $_GET['action'] = '';
        $messageStack->add_session(ERROR_TAX_RATE_EXISTS, 'caution');
      } else {
        $db->Execute("DELETE FROM " . TABLE_GEO_ZONES . "
                      WHERE geo_zone_id = " . (int)$zID);

        $db->Execute("DELETE FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                      WHERE geo_zone_id = " . (int)$zID);
      }

      zen_redirect(zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage']));
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
    <?php
    if (isset($_GET['zID']) && (($saction == 'edit') || ($saction == 'new'))) {
      ?>
      <script>
        function resetZoneSelected(theForm) {
            if (theForm.state.value != '') {
                theForm.zone_id.selectedIndex = '0';
                if (theForm.zone_id.options.length > 0) {
                    theForm.state.value = '<?php echo JS_STATE_SELECT; ?>';
                }
            }
        }

        function update_zone(theForm) {
            var NumState = theForm.zone_id.options.length;
            var SelectedCountry = '';

            while (NumState > 0) {
                NumState--;
                theForm.zone_id.options[NumState] = null;
            }

            SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

  <?php echo zen_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

        }
      </script>
      <?php
    }
    ?>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
      // -->
    </script>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <p><?php if (!empty($_GET['zID'])) echo zen_get_geo_zone_name($_GET['zID']); ?></p>
      <!-- body_text //-->
      <?php
      if ($action == 'list') {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $zones_query_raw = "SELECT a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name
                                      FROM (" . TABLE_ZONES_TO_GEO_ZONES . " a
                                        LEFT JOIN " . TABLE_COUNTRIES . " c ON a.zone_country_id = c.countries_id
                                        LEFT JOIN " . TABLE_ZONES . " z ON a.zone_id = z.zone_id)
                                      WHERE a.geo_zone_id = " . (int)$_GET['zID'] . "
                                      ORDER BY c.countries_name, association_id";
// Split Page
// reset page when page is unknown
                  if ((!isset($_GET['spage']) or $_GET['spage'] == '' or $_GET['spage'] == '1') && !empty($_GET['sID'])) {
                    $check_page = $db->Execute($zones_query_raw);
                    $check_count = 1;
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                      foreach ($check_page as $item) {
                        if ($item['association_id'] == $_GET['sID']) {
                          break;
                        }
                        $check_count++;
                      }
                      $_GET['spage'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                    } else {
                      $_GET['spage'] = 1;
                    }
                  }
                  $zones_split = new splitPageResults($_GET['spage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
                  $zones = $db->Execute($zones_query_raw);
                  foreach ($zones as $zone) {
                    if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $zone['association_id']))) && !isset($sInfo) && (substr($action, 0, 3) != 'new')) {
                      $sInfo = new objectInfo($zone);
                    }
                    if (isset($sInfo) && is_object($sInfo) && ($zone['association_id'] == $sInfo->association_id)) {
                      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'" role="button">' . "\n";
                    } else {
                      echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zone['association_id']) . '\'" role="button">' . "\n";
                    }
                    ?>
                <td class="dataTableContent"><?php echo (($zone['countries_name']) ? $zone['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent"><?php echo (($zone['zone_id']) ? $zone['zone_name'] : TEXT_ALL_ZONES); ?></td>
                <td class="dataTableContent text-right">
                    <?php
                    if (isset($sInfo) && is_object($sInfo) && ($zone['association_id'] == $sInfo->association_id)) {
                      echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zone['association_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                    }
                    ?>&nbsp;
                </td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
          </div>
          <?php
        } else {
          ?>
          <div class="row">
              <?php echo TEXT_LEGEND . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') . TEXT_LEGEND_TAX_AND_ZONES . '&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') . TEXT_LEGEND_ONLY_ZONES . '&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif') . TEXT_LEGEND_NOT_CONF; ?>
          </div>
          <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
              <table class="table table-hover">
                <thead>
                  <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES_DESCRIPTION; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                  </tr>
                </thead>
                <tbody>
                    <?php
                    $zones_query_raw = "SELECT geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added
                                        FROM " . TABLE_GEO_ZONES . "
                                        ORDER BY geo_zone_name";
// Split Page
// reset page when page is unknown
                    if ((!isset($_GET['zpage']) or $_GET['zpage'] == '' or $_GET['zpage'] == '1') && !empty($_GET['zID'])) {
                      $check_page = $db->Execute($zones_query_raw);
                      $check_count = 1;
                      if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                        foreach ($check_page as $item) {
                          if ($item['geo_zone_id'] == $_GET['zID']) {
                            break;
                          }
                          $check_count++;
                        }
                        $_GET['zpage'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                      } else {
                        $_GET['zpage'] = 1;
                      }
                    }
                    $zones_split = new splitPageResults($_GET['zpage'], MAX_DISPLAY_SEARCH_RESULTS, $zones_query_raw, $zones_query_numrows);
                    $zones = $db->Execute($zones_query_raw);
                    foreach ($zones as $zone) {
                      $num_zones = $db->Execute("SELECT COUNT(*) AS num_zones
                                                 FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                                                 WHERE geo_zone_id = " . (int)$zone['geo_zone_id'] . "
                                                 GROUP BY geo_zone_id");

                      if (!$num_zones->EOF && $num_zones->fields['num_zones'] > 0) {
                        $zone['num_zones'] = $num_zones->fields['num_zones'];
                      } else {
                        $zone['num_zones'] = 0;
                      }

                      $num_tax_rates = $db->Execute("SELECT COUNT(*) AS num_tax_rates
                                                     FROM " . TABLE_TAX_RATES . "
                                                     WHERE tax_zone_id = " . (int)$zone['geo_zone_id'] . "
                                                     GROUP BY tax_zone_id");

                      if (!$num_tax_rates->EOF) { 
                        $zone['num_tax_rates'] = $num_tax_rates->fields['num_tax_rates'];
                      } else {
                        $zone['num_tax_rates'] = 0;
                      }

                      if ((!isset($_GET['zID']) || (isset($_GET['zID']) && ($_GET['zID'] == $zone['geo_zone_id']))) && !isset($zInfo) && (substr($action, 0, 3) != 'new')) {
                        $zInfo = new objectInfo($zone);
                      }
                      if (isset($zInfo) && is_object($zInfo) && ($zone['geo_zone_id'] == $zInfo->geo_zone_id)) {
                        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'" role="button">' . "\n";
                      } else {
                        echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zone['geo_zone_id']) . '\'" role="button">' . "\n";
                      }
                      ?>
                  <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zone['geo_zone_id'] . '&action=list') . '">' . zen_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . $zone['geo_zone_name']; ?></td>
                  <td class="dataTableContent"><?php echo $zone['geo_zone_description']; ?></td>
                  <td class="dataTableContent text-center"><?php
                      // show current status
                      if ($zone['num_tax_rates'] && $zone['num_zones']) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif');
                      } elseif ($zone['num_zones']) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif');
                      } else {
                        echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif');
                      }
                      ?></td>
                  <td class="dataTableContent text-right">
                      <?php
                      if (isset($zInfo) && is_object($zInfo) && ($zone['geo_zone_id'] == $zInfo->geo_zone_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zone['geo_zone_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                      }
                      ?>&nbsp;
                  </td>
                  </tr>
                  <?php
                }
                ?>
                </tbody>
              </table>
            </div>
            <?php
          }
          ?>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = array();
              $contents = array();

              if ($action == 'list') {
                switch ($saction) {
                  case 'new':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($_GET['sID']) ? 'sID=' . $_GET['sID'] . '&' : '') . '&saction=insert_sub', 'post', 'class="form-horizontal"'));
                    $contents[] = array('text' => TEXT_INFO_NEW_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY, 'zone_country_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_country_id', zen_get_countries(TEXT_ALL_COUNTRIES), '', 'onChange="update_zone(this.form);"' . ' class="form-control"'));
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY_ZONE, 'zone_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down(), '', 'class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($_GET['sID']) ? 'sID=' . $_GET['sID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'edit':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub', 'post', 'class="form-horizontal"'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY, 'zone_country_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_country_id', zen_get_countries(TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onChange="update_zone(this.form);" class="form-control"'));
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_COUNTRY_ZONE, 'zone_id', 'class="control-label"') . zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id, 'class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'delete':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&saction=deleteconfirm_sub') . zen_draw_hidden_field('sID', $sInfo->association_id));
                    $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br><b>' . $sInfo->countries_name . '</b>');
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  default:
                    if (isset($sInfo) && is_object($sInfo)) {
                      $heading[] = array('text' => '<h4>' . $sInfo->countries_name . '</h4>');

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                      $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->date_added));
                      if (zen_not_null($sInfo->last_modified)) {
                        $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($sInfo->last_modified));
                      }
                    }
                    break;
                }
              } else {
                switch ($action) {
                  case 'new_zone':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_NEW_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=insert_zone', 'post', 'class="form-horizontal"'));
                    $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_NAME, 'geo_zone_name', 'class="control-label"') . zen_draw_input_field('geo_zone_name', '', zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_name') . ' class="form-control"'));
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_DESCRIPTION, 'geo_zone_description', 'class="control-label"') . zen_draw_input_field('geo_zone_description', '', zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_description') . ' class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'edit_zone':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone', 'post', 'class="form-horizontal"'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_NAME, 'geo_zone_name', 'class="control-label"') . zen_draw_input_field('geo_zone_name', htmlspecialchars($zInfo->geo_zone_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_name') . ' class="form-control"'));
                    $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_ZONE_DESCRIPTION, 'geo_zone_description', 'class="control-label"') . zen_draw_input_field('geo_zone_description', htmlspecialchars($zInfo->geo_zone_description, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_GEO_ZONES, 'geo_zone_description') . ' class="form-control"'));
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  case 'delete_zone':
                    $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_ZONE . '</h4>');

                    $contents = array('form' => zen_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&action=deleteconfirm_zone') . zen_draw_hidden_field('zID', $zInfo->geo_zone_id));
                    $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
                    $contents[] = array('text' => '<br><b>' . $zInfo->geo_zone_name . '</b>');
                    $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                    break;
                  default:
                    if (isset($zInfo) && is_object($zInfo)) {
                      $heading[] = array('text' => '<h4>' . $zInfo->geo_zone_name . '</h4>');

                      $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>' . ' <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a>');
                      $contents[] = array('align' => 'text-center', 'text' => ($zInfo->num_tax_rates > 0 ? '<a href="' . zen_href_link(FILENAME_TAX_RATES, '', 'NONSSL') . '" class="btn btn-info" role="button">' . IMAGE_TAX_RATES . '</a>' : ''));
                      $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones);
                      $contents[] = array('text' => '<br>' . TEXT_INFO_NUMBER_TAX_RATES . ' ' . $zInfo->num_tax_rates);
                      $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($zInfo->date_added));
                      if (zen_not_null($zInfo->last_modified)) {
                        $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($zInfo->last_modified));
                      }
                      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_DESCRIPTION . '<br>' . $zInfo->geo_zone_description);
                    }
                    break;
                }
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
            <?php if ($action == 'list') { ?>
            <table class="table">
              <tr>
                <td><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?></td>
                <td class="text-right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list', 'spage'); ?></td>
              </tr>
              <tr>
                <td class="text-right" colspan="2"><?php if (empty($saction)) echo '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '" class="btn btn-default" role="button">' . IMAGE_BACK . '</a> <a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&' . (isset($sInfo) ? 'sID=' . $sInfo->association_id . '&' : '') . 'saction=new') . '" class="btn btn-primary" role="button">' . IMAGE_INSERT . '</a>'; ?></td>
              </tr>
            </table>
          <?php } else { ?>
            <table class="table">
              <tr>
                <td><?php echo $zones_split->display_count($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['zpage'], TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?></td>
                <td class="text-right"><?php echo $zones_split->display_links($zones_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['zpage'], '', 'zpage'); ?></td>
              </tr>
              <tr>
                <td class="text-right" colspan="2"><?php if (!$action) echo '<a href="' . zen_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone') . '" class="btn btn-primary" role="button">' . IMAGE_INSERT . '</a>'; ?></td>
              </tr>
            </table>
          <?php } ?>
        </div>
      </div>
      <!-- body_eof //-->

      <!-- footer //-->
      <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
      <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
