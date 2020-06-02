<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 23 Modified in v1.5.7 $
 */
//
define('AUTOCHECK', 'False');

require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$specials_condition_array = array(
  array('id' => '0', 'text' => SPECIALS_CONDITION_DROPDOWN_0),
  array('id' => '1', 'text' => SPECIALS_CONDITION_DROPDOWN_1),
  array('id' => '2', 'text' => SPECIALS_CONDITION_DROPDOWN_2));

$deduction_type_array = array(
  array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {
  switch ($action) {
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
        $salemaker_data_array = array(
          'sale_status' => zen_db_prepare_input($_POST['flag']),
          'sale_date_last_modified' => 'now()',
          'sale_date_status_change' => 'now()');
        zen_db_perform(TABLE_SALEMAKER_SALES, $salemaker_data_array, 'update', "sale_id = " . (int)$_GET['sID']);
        // update prices for products in sale
        zen_update_salemaker_product_prices($_GET['sID']);
        zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $_GET['sID'], 'NONSSL'));
      }
      break;
    case 'insert':
    case 'update':
// insert a new sale or update an existing sale
// Create a string of all affected (sub-)categories
      if (zen_not_null($_POST['categories'])) {
        $categories_selected = array();
        $categories_all = array();
        foreach (zen_db_prepare_input($_POST['categories']) as $category_path) {
          $tmp = explode('_', substr($category_path, 0, strlen($category_path) - 1));
          $category = array_pop($tmp);
          $categories_selected[] = $category;
          $categories_all[] = $category;
          foreach (zen_get_category_tree($category) as $subcategory) {
            if ($subcategory['id'] != '0') {
              $categories_all[] = $subcategory['id'];
            }
          }
        }
        asort($categories_selected);
        $categories_selected_string = implode(',', array_unique($categories_selected));
        asort($categories_all);
        $categories_all_string = ',' . implode(',', array_unique($categories_all)) . ',';
      } else {
        $categories_selected_string = 'null';
        $categories_all_string = 'null';
      }

      $salemaker_sales_data_array = array(
        'sale_name' => substr(zen_db_prepare_input($_POST['name']), 0, 128),
        'sale_deduction_value' => zen_db_prepare_input((float)$_POST['deduction']),
        'sale_deduction_type' => zen_db_prepare_input($_POST['type']),
        'sale_pricerange_from' => zen_db_prepare_input((float)$_POST['from']),
        'sale_pricerange_to' => zen_db_prepare_input((float)$_POST['to']),
        'sale_specials_condition' => zen_db_prepare_input($_POST['condition']),
        'sale_categories_selected' => $categories_selected_string,
        'sale_categories_all' => $categories_all_string,
        'sale_date_start' => ((zen_db_prepare_input($_POST['start']) == '') ? '0001-01-01' : zen_date_raw($_POST['start'])),
        'sale_date_end' => ((zen_db_prepare_input($_POST['end']) == '') ? '0001-01-01' : zen_date_raw($_POST['end'])));

      if ($action == 'insert') {
        $salemaker_sales_data_array['sale_status'] = 1;
        $salemaker_sales_data_array['sale_date_added'] = 'now()';
        $salemaker_sales_data_array['sale_date_last_modified'] = '0001-01-01';
        $salemaker_sales_data_array['sale_date_status_change'] = '0001-01-01';
        zen_db_perform(TABLE_SALEMAKER_SALES, $salemaker_sales_data_array, 'insert');

        $_POST['sID'] = $db->Insert_ID();
      } else {
        $salemaker_sales_data_array['sale_date_last_modified'] = 'now()';
        zen_db_perform(TABLE_SALEMAKER_SALES, $salemaker_sales_data_array, 'update', "sale_id = " . zen_db_input($_POST['sID']));
      }

      // update prices for products in sale
      zen_update_salemaker_product_prices($_POST['sID']);

      zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $_POST['sID']));
      break;
    case 'copyconfirm':
      $newname = zen_db_prepare_input($_POST['newname']);
      if (zen_not_null($newname)) {
        $salemaker_sales = $db->Execute("SELECT *
                                         FROM " . TABLE_SALEMAKER_SALES . "
                                         WHERE sale_id = " . zen_db_input($_GET['sID']));
        if ($salemaker_sales->RecordCount() > 0) {

          $sql_data_array = array(
            'sale_id' => 'null',
            'sale_status' => 0,
            'sale_name' => $newname,
            'sale_date_added' => 'now()',
            'sale_date_last_modified' => '0001-01-01',
            'sale_date_status_change' => '0001-01-01',
            'sale_deduction_value' => (float)$salemaker_sales->fields['sale_deduction_value'],
            'sale_deduction_type' => (float)$salemaker_sales->fields['sale_deduction_type'],
            'sale_pricerange_from' => (float)$salemaker_sales->fields['sale_pricerange_from'],
            'sale_pricerange_to' => (float)$salemaker_sales->fields['sale_pricerange_to'],
            'sale_specials_condition' => (int)$salemaker_sales->fields['sale_specials_condition'],
            'sale_categories_selected' => $salemaker_sales->fields['sale_categories_selected'],
            'sale_categories_all' => $salemaker_sales->fields['sale_categories_all'],
            'sale_date_start' => $salemaker_sales->fields['sale_date_start'],
            'sale_date_end' => $salemaker_sales->fields['sale_date_end']
          );

          zen_db_perform(TABLE_SALEMAKER_SALES, $sql_data_array, 'insert');

          $sale_id = $db->insert_ID();
          // update prices for products in sale
          zen_update_salemaker_product_prices($sale_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $db->insert_ID()));
      break;
    case 'deleteconfirm':
      $sale_id = zen_db_prepare_input($_POST['sID']);

      // set sale off to update prices before removing
      $db->Execute("UPDATE " . TABLE_SALEMAKER_SALES . "
                    SET sale_status = 0
                    WHERE sale_id = " . (int)$sale_id);

      // update prices for products in sale
      zen_update_salemaker_product_prices($sale_id);

      $db->Execute("DELETE FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_id = " . (int)$sale_id);

      zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page']));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
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
    <?php
    if (($action == 'new') || ($action == 'edit')) {
      ?>
      <link rel="stylesheet" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
      <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
      <script>
        function session_win() {
            window.open("<?php echo zen_href_link(FILENAME_SALEMAKER_INFO); ?>", "salemaker_info", "height=460,width=600,scrollbars=yes,resizable=yes").focus();
        }
        function popupWindow(url) {
            window.open(url, 'popupWindow', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=600,height=460,screenX=150,screenY=150,top=150,left=150')
        }
<?php /*
//        function session_win1() {
//            window.open("<?php echo zen_href_link(FILENAME_SALEMAKER_POPUP, 'cid=' . $category['categories_id']); ?>//", "salemaker_info", "height=460,width=600,scrollbars=yes,resizable=yes").focus();
//        }
*/?>
        function init() {
            cssjsmenu('navbar');
            if (document.getElementById) {
                var kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
        }
        function RowClick(RowValue) {
            for (i = 0; i < document.sale_form.length; i++) {
                if (document.sale_form.elements[i].type == 'checkbox') {
                    if (document.sale_form.elements[i].value == RowValue) {
                        if (document.sale_form.elements[i].disabled == false) {
                            document.sale_form.elements[i].checked = !document.sale_form.elements[i].checked;
                        }
                    }
                }
            }
            SetCategories()
        }

        function CheckBoxClick() {
            if (this.disabled == false) {
                this.checked = !this.checked;
            }
            SetCategories()
        }

        function SetCategories() {
            for (i = 0; i < document.sale_form.length; i++) {
                if (document.sale_form.elements[i].type == 'checkbox') {
                    document.sale_form.elements[i].disabled = false;
                    document.sale_form.elements[i].onclick = CheckBoxClick;
                    document.sale_form.elements[i].parentNode.parentNode.className = 'SaleMakerOver';
                }
            }
            change = true;
            while (change) {
                change = false;
                for (i = 0; i < document.sale_form.length; i++) {
                    if (document.sale_form.elements[i].type == 'checkbox') {
                        currentcheckbox = document.sale_form.elements[i];
                        currentrow = currentcheckbox.parentNode.parentNode;
                        if ((currentcheckbox.checked) && (currentrow.className == 'SaleMakerOver')) {
                            currentrow.className = 'SaleMakerSelected';
                            for (j = 0; j < document.sale_form.length; j++) {
                                if (document.sale_form.elements[j].type == 'checkbox') {
                                    relatedcheckbox = document.sale_form.elements[j];
                                    relatedrow = relatedcheckbox.parentNode.parentNode;
                                    if ((relatedcheckbox != currentcheckbox) && (relatedcheckbox.value.substr(0, currentcheckbox.value.length) == currentcheckbox.value)) {
                                        if (!relatedcheckbox.disabled) {
  <?php
  if ((defined('AUTOCHECK')) && (AUTOCHECK == 'True')) {
    ?>
                                              relatedcheckbox.checked = true;
    <?php
  }
  ?>
                                            relatedcheckbox.disabled = true;
                                            relatedrow.className = 'SaleMakerDisabled';
                                            change = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

      </script>
    </head>
<?php } ?>
<?php if ($action == 'new' || $action == 'edit') { ?>
       <body onload="SetCategories(); SetFocus(); init()">
       <div id="spiffycalendar" class="text"></div>
<?php } else { ?>
       <body onload="SetFocus(); init()">
<?php } ?>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <div class="container-fluid">
    <!-- body //-->
    <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>

    <!-- body_text //-->
    <?php
    if (($action == 'new') || ($action == 'edit')) {
      $form_action = 'insert';
      if (($action == 'edit') && ($_GET['sID'])) {
        $form_action = 'update';

        $salemaker_sales = $db->Execute("SELECT sale_id, sale_status, sale_name, sale_deduction_value, sale_deduction_type,
                                                sale_pricerange_from, sale_pricerange_to, sale_specials_condition,
                                                sale_categories_selected, sale_categories_all, sale_date_start, sale_date_end,
                                                sale_date_added, sale_date_last_modified, sale_date_status_change
                                         FROM " . TABLE_SALEMAKER_SALES . "
                                         WHERE sale_id = " . (int)$_GET['sID']);

        $sInfo = new objectInfo($salemaker_sales->fields);
      } else {
        $sInfo = new objectInfo(array());
      }
      ?>
      <script>
        var StartDate = new ctlSpiffyCalendarBox("StartDate", "sale_form", "start", "btnDate1", "<?php echo (($sInfo->sale_date_start == '0001-01-01') ? '' : zen_date_short($sInfo->sale_date_start)); ?>", scBTNMODE_CUSTOMBLUE);
        var EndDate = new ctlSpiffyCalendarBox("EndDate", "sale_form", "end", "btnDate2", "<?php echo (($sInfo->sale_date_end == '0001-01-01') ? '' : zen_date_short($sInfo->sale_date_end)); ?>", scBTNMODE_CUSTOMBLUE);
      </script>
      <?php echo zen_draw_form("sale_form", FILENAME_SALEMAKER, zen_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action, 'post', 'onsubmit="return check_dates(start,StartDate.required, end, EndDate.required);" class="form-horizontal"'); ?>
      <?php if ($form_action == 'update') echo zen_draw_hidden_field('sID', $_GET['sID']); ?>
      <div class="row">
        <div class="col-sm-6"><?php echo TEXT_SALEMAKER_POPUP; ?></div>
        <div class="col-sm-6 text-right">
          <button type="submit" class="btn btn-primary"><?php echo (($form_action == 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button> <a href="<?php echo zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . ($_GET['sID'] > 0 ? '&sID=' . $_GET['sID'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a></div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_NAME, 'name', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('name', htmlspecialchars($sInfo->sale_name, ENT_COMPAT, CHARSET, TRUE), 'size="37" class="form-control"'); ?>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_DEDUCTION, 'deduction', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <div class="col-sm-5"><?php echo zen_draw_input_field('deduction', $sInfo->sale_deduction_value, 'size="8" class="form-control"'); ?></div>
          <div class="col-sm-2"><?php echo TEXT_SALEMAKER_DEDUCTION_TYPE; ?></div>
          <div class="col-sm-5"><?php echo zen_draw_pull_down_menu('type', $deduction_type_array, $sInfo->sale_deduction_type, 'class="form-control"'); ?></div>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_PRICERANGE_FROM, 'from', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <div class="col-sm-5"><?php echo zen_draw_input_field('from', $sInfo->sale_pricerange_from, 'size="8" class="form-control"'); ?></div>
          <div class="col-sm-2"><?php echo TEXT_SALEMAKER_PRICERANGE_TO; ?></div>
          <div class="col-sm-5"><?php echo zen_draw_input_field('to', $sInfo->sale_pricerange_to, 'size="8" class="form-control"'); ?></div>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_SPECIALS_CONDITION, 'condition', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('condition', $specials_condition_array, $sInfo->sale_specials_condition, 'class="form-control"'); ?>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_DATE_START, 'start', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <script>
            StartDate.writeControl();
            StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
          </script>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SALEMAKER_DATE_END, 'end', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <script>
            EndDate.writeControl();
            EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
          </script>
        </div>
      </div>
      <?php
      $categories_array = zen_get_category_tree('0', '&nbsp;&nbsp;', '0');
      $n = sizeof($categories_array);
      for ($i = 0; $i < $n; $i++) {
        $parents = $db->Execute("SELECT parent_id
                                 FROM " . TABLE_CATEGORIES . "
                                 WHERE categories_id = " . (int)$categories_array[$i]['id']);
        $categories_array[$i]['parent_id'] = $parents->fields['parent_id'];
        $categories_array[$i]['categories_id'] = $categories_array[$i]['id'];
        $categories_array[$i]['path'] = $categories_array[$i]['categories_id'];
        $categories_array[$i]['indent'] = 0;
        $parent = $categories_array[$i]['parent_id'];
        while ($parent != 0) {
          $categories_array[$i]['indent'] ++;
          for ($j = 0; $j < $n; $j++) {
            if ($categories_array[$j]['categories_id'] == $parent) {
              $categories_array[$i]['path'] = $parent . '_' . $categories_array[$i]['path'];
              $parent = $categories_array[$j]['parent_id'];
              break;
            }
          }
        }
        $categories_array[$i]['path'] = $categories_array[$i]['path'] . '_';
      }
      $categories_selected = explode(',', $sInfo->sale_categories_selected);
      if (zen_not_null($sInfo->sale_categories_selected)) {
        $selected = in_array(0, $categories_selected);
      } else {
        $selected = false;
      }

      $prev_sales = $db->Execute("SELECT sale_categories_all
                                  FROM " . TABLE_SALEMAKER_SALES);
      foreach ($prev_sales as $prev_sale) {
        $prev_categories = explode(',', $prev_sale['sale_categories_all']);
        foreach ($prev_categories as $key => $value) {
            if ($value && isset($prev_categories_array[$value])) {
                $prev_categories_array[$value] ++;
            } else {
                $prev_categories_array[$value] = 1;
            }
        }
      }

// set Entire Catalog when set
      if (empty($sInfo->sale_categories_selected) && !empty($sInfo->sale_categories_all)) {
        $zc_check_all_cats = 1;
      } else {
        $zc_check_all_cats = 0;
      }
      ?>
      <div class="form-group">
        <div class="col-sm-offset-3">
            <?php echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); ?><?php echo TEXT_SALEMAKER_ENTIRE_CATALOG; ?>
        </div>
      </div>
      <div class="form-group" onClick="RowClick('0')">
        <div class="col-sm-offset-3">
          <div>
            <label><?php echo zen_draw_checkbox_field('categories[]', '0', $zc_check_all_cats); ?><?php echo TEXT_SALEMAKER_TOP; ?></label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-3">
            <?php echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); ?><?php echo TEXT_SALEMAKER_CATEGORIES; ?>
        </div>
      </div>
      <?php
      foreach ($categories_array as $category) {
        if (zen_not_null($sInfo->sale_categories_selected)) {
          $selected = in_array($category['categories_id'], $categories_selected);
        } else {
          $selected = false;
        }
        ?>
        <div class="form-group row">
          <div class="col-sm-offset-3 col-xs-5 col-sm-4 col-md-4" onClick="RowClick('<?php echo $category['path'];
          ?>')">
            <div class="checkbox">
              <label><?php echo zen_draw_checkbox_field('categories[]', $category['path'], $selected); ?><?php echo $category['text']; ?></label>
              <?php
              if (isset($prev_categories_array[$category['categories_id']]) && $prev_categories_array[$category['categories_id']]) {
                echo sprintf(TEXT_WARNING_SALEMAKER_PREVIOUS_CATEGORIES, $prev_categories_array[$category['categories_id']]);
              } ?>
            </div>
          </div>
          <div class="col-xs-3 col-sm-3 col-md-1">
          <?php
          if (isset($prev_categories_array[$category['categories_id']]) && $prev_categories_array[$category['categories_id']]) {
          ?>
            <a href="javascript:popupWindow('<?php echo zen_href_link(FILENAME_SALEMAKER_POPUP, 'cid=' . $category['categories_id']); ?>')"><?php echo TEXT_MORE_INFO; ?></a>
          <?php } ?>
          </div>
        </div>
      <?php } ?>
      <?php echo '</form>'; ?>
      <?php
    } else {
      ?>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-striped table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SALE_NAME; ?></th>
                <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_SALE_DEDUCTION; ?></th>
                <th class="dataTableHeadingContent"></td>
                <th class="dataTableHeadingContent center"><?php echo TABLE_HEADING_SALE_DATE_START; ?></th>
                <th class="dataTableHeadingContent center"><?php echo TABLE_HEADING_SALE_DATE_END; ?></th>
                <th class="dataTableHeadingContent center"><?php echo TABLE_HEADING_STATUS; ?></th>
                <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php
                $salemaker_sales_query_raw = "SELECT sale_id, sale_status, sale_name, sale_deduction_value, sale_deduction_type, sale_pricerange_from,
                                                     sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_categories_all, sale_date_start,
                                                     sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change
                                              FROM " . TABLE_SALEMAKER_SALES . "
                                              ORDER BY sale_name";
                $salemaker_sales_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $salemaker_sales_query_raw, $salemaker_sales_query_numrows);
                $salemaker_sales = $db->Execute($salemaker_sales_query_raw);
                foreach ($salemaker_sales as $salemaker_sale) {
                  if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $salemaker_sale['sale_id']))) && !isset($sInfo)) {
                    $sInfo_array = $salemaker_sale;
                    $sInfo = new objectInfo($sInfo_array);
                  }

                  if (isset($sInfo) && is_object($sInfo) && ($salemaker_sale['sale_id'] == $sInfo->sale_id)) {
                    ?>
                  <tr class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=edit'); ?>'">
                    <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $salemaker_sale['sale_id']); ?>'">
                    <?php } ?>
                  <td  class="dataTableContent" align="left"><?php echo $salemaker_sale['sale_name']; ?></td>
                  <td  class="dataTableContent" align="right"><?php echo $salemaker_sale['sale_deduction_value']; ?></td>
                  <td  class="dataTableContent" align="left"><?php echo $deduction_type_array[$salemaker_sale['sale_deduction_type']]['text']; ?></td>
                  <td  class="dataTableContent" align="center"><?php echo (($salemaker_sale['sale_date_start'] == '0001-01-01') ? TEXT_SALEMAKER_IMMEDIATELY : zen_date_short($salemaker_sale['sale_date_start'])); ?></td>
                  <td  class="dataTableContent" align="center"><?php echo (($salemaker_sale['sale_date_end'] == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($salemaker_sale['sale_date_end'])); ?></td>
                  <td  class="dataTableContent" align="center">
                      <?php
                      if ($salemaker_sale['sale_status'] == '1') {
                        echo zen_draw_form('setflag_products', FILENAME_SALEMAKER, 'action=setflag&sID=' . $salemaker_sale['sale_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                        ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
                      <input type="hidden" name="flag" value="0" />
                      <?php echo '</form>'; ?>
                      <?php
                    } else {
                      echo zen_draw_form('setflag_products', FILENAME_SALEMAKER, 'action=setflag&sID=' . $salemaker_sale['sale_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));
                      ?>
                      <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
                      <input type="hidden" name="flag" value="1" />
                      <?php echo '</form>'; ?>
                      <?php
                    }
                    ?>
                  </td>
                  <td class="dataTableContent" align="right"><?php
                      if (!empty($sInfo) && (is_object($sInfo)) && !empty($salemaker_sale) && isset($salemaker_sale['sale_id']) && ($salemaker_sale['sale_id'] == $sInfo->sale_id)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $salemaker_sale['sale_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
              case 'copy':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_COPY_SALE . '</h4>');

                $contents = array('form' => zen_draw_form('sales', FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=copyconfirm'));
                $contents[] = array('text' => zen_draw_label(sprintf(TEXT_INFO_COPY_INTRO, $sInfo->sale_name), 'newname', 'class="control-label"'));
                $contents[] = array('text' => zen_draw_input_field('newname', htmlspecialchars($sInfo->sale_name . '_', ENT_COMPAT, CHARSET, TRUE), 'size="31" class="form-control"'));
                $contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_COPY . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_SALE . '</h4>');

                $contents = array('form' => zen_draw_form('sales', FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('sID', $sInfo->sale_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $sInfo->sale_name . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
                break;
              default:
                if (is_object($sInfo)) {
                  $heading[] = array('text' => '<h4>' . $sInfo->sale_name . '</h4>');

                  $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=copy') . '" class="btn btn-primary" role="button">' . IMAGE_COPY_TO . '</a> <a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($sInfo->sale_date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_DATE_MODIFIED . ' ' . (($sInfo->sale_date_last_modified == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($sInfo->sale_date_last_modified)));
                  $contents[] = array('text' => '' . TEXT_INFO_DATE_STATUS_CHANGE . ' ' . (($sInfo->sale_date_status_change == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($sInfo->sale_date_status_change)));

                  $contents[] = array('text' => '<br>' . TEXT_INFO_DEDUCTION . ' ' . $sInfo->sale_deduction_value . ' ' . $deduction_type_array[$sInfo->sale_deduction_type]['text']);
                  $contents[] = array('text' => '' . TEXT_INFO_PRICERANGE_FROM . ' ' . $currencies->format($sInfo->sale_pricerange_from) . TEXT_INFO_PRICERANGE_TO . $currencies->format($sInfo->sale_pricerange_to));
                  $contents[] = array('text' => '<table class="dataTableContent" border="0" width="100%" cellspacing="0" cellpadding="0"><tr><td valign="top">' . TEXT_INFO_SPECIALS_CONDITION . '&nbsp;</td><td>' . $specials_condition_array[$sInfo->sale_specials_condition]['text'] . '</td></tr></table>');

                  $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_START . ' ' . (($sInfo->sale_date_start == '0001-01-01') ? TEXT_SALEMAKER_IMMEDIATELY : zen_date_short($sInfo->sale_date_start)));
                  $contents[] = array('text' => '' . TEXT_INFO_DATE_END . ' ' . (($sInfo->sale_date_end == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($sInfo->sale_date_end)));
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
            <td><?php echo $salemaker_sales_split->display_count($salemaker_sales_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SALES); ?></td>
            <td class="text-right"><?php echo $salemaker_sales_split->display_links($salemaker_sales_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php
          if (empty($action)) {
            ?>
            <tr>
              <td colspan="2" class="text-right"><?php echo '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_NEW_SALE . '</a>'; ?></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
      <?php
    }
    ?>
    <!-- body_text_eof //-->
  </div>
  <!-- body_eof //-->

  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
