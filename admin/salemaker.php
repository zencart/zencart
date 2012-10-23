<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: salemaker.php 19330 2011-08-07 06:32:56Z drbyte $
 */
//
define('AUTOCHECK', 'False');

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $specials_condition_array = array(array('id' => '0', 'text' => SPECIALS_CONDITION_DROPDOWN_0),
                                    array('id' => '1', 'text' => SPECIALS_CONDITION_DROPDOWN_1),
                                    array('id' => '2', 'text' => SPECIALS_CONDITION_DROPDOWN_2));

  $deduction_type_array = array(array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
                                array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
                                array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0))
        {
          $salemaker_data_array = array('sale_status' => zen_db_prepare_input($_POST['flag']),
                                      'sale_date_last_modified' => 'now()',
                                      'sale_date_status_change' => 'now()');
          zen_db_perform(TABLE_SALEMAKER_SALES, $salemaker_data_array, 'update', "sale_id = '" . zen_db_prepare_input($_GET['sID']) . "'");
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
          foreach(zen_db_prepare_input($_POST['categories']) as $category_path) {
            $tmp = explode('_', substr($category_path,0,strlen($category_path)-1));
            $category = array_pop($tmp);
            $categories_selected[] = $category;
            $categories_all[] = $category;
            foreach(zen_get_category_tree($category) as $subcategory) {
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

        $salemaker_sales_data_array = array('sale_name' => zen_db_prepare_input($_POST['name']),
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
          zen_db_perform(TABLE_SALEMAKER_SALES, $salemaker_sales_data_array, 'update', "sale_id = '" . zen_db_input($_POST['sID']) . "'");
        }

        // update prices for products in sale
        zen_update_salemaker_product_prices($_POST['sID']);

        zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $_POST['sID']));
        break;
      case 'copyconfirm':
        $newname = zen_db_prepare_input($_POST['newname']);
        if (zen_not_null($newname)) {
          $salemaker_sales = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . " where sale_id = '" . zen_db_input($_GET['sID']) . "'");
          if ($salemaker_sales->RecordCount() > 0) {

          $sql_data_array = array('sale_id' => 'null',
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

            $sale_id = $db->Insert_ID();
            // update prices for products in sale
            zen_update_salemaker_product_prices($sale_id);
          }
        }

        zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $db->Insert_ID()));
        break;
      case 'deleteconfirm':
        $sale_id = zen_db_prepare_input($_POST['sID']);

        // set sale off to update prices before removing
        $db->Execute("update " . TABLE_SALEMAKER_SALES . " set sale_status=0 where sale_id='" . $sale_id . "'");

        // update prices for products in sale
        zen_update_salemaker_product_prices($sale_id);

        $db->Execute("delete from " . TABLE_SALEMAKER_SALES . " where sale_id = '" . (int)$sale_id . "'");

        zen_redirect(zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page']));
        break;
    }
  }
require('includes/admin_html_head.php');

  if ( ($action == 'new') || ($action == 'edit') ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="JavaScript">
function session_win() {
  window.open("<?php echo zen_href_link(FILENAME_SALEMAKER_INFO); ?>","salemaker_info","height=460,width=600,scrollbars=yes,resizable=yes").focus();
}
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=600,height=460,screenX=150,screenY=150,top=150,left=150')
}
function session_win1() {
  window.open("<?php echo zen_href_link(FILENAME_SALEMAKER_POPUP, 'cid='.$category['categories_id']); ?>","salemaker_info","height=460,width=600,scrollbars=yes,resizable=yes").focus();
}
function RowClick(RowValue) {
  for (i=0; i<document.sale_form.length; i++) {
    if(document.sale_form.elements[i].type == 'checkbox') {
      if(document.sale_form.elements[i].value == RowValue) {
        if(document.sale_form.elements[i].disabled == false) {
         document.sale_form.elements[i].checked = !document.sale_form.elements[i].checked;
        }
      }
    }
  }
  SetCategories()
}

function CheckBoxClick() {
  if(this.disabled == false) {
    this.checked = !this.checked;
  }
  SetCategories()
}

function SetCategories() {
  for (i=0; i<document.sale_form.length; i++) {
    if(document.sale_form.elements[i].type == 'checkbox') {
      document.sale_form.elements[i].disabled = false;
    document.sale_form.elements[i].onclick = CheckBoxClick;
      document.sale_form.elements[i].parentNode.parentNode.className = 'SaleMakerOver';
    }
  }
  change = true;
  while(change) {
    change = false;
    for (i=0; i<document.sale_form.length; i++) {
      if(document.sale_form.elements[i].type == 'checkbox') {
        currentcheckbox = document.sale_form.elements[i];
        currentrow = currentcheckbox.parentNode.parentNode;
        if ( (currentcheckbox.checked) && (currentrow.className == 'SaleMakerOver') ) {
          currentrow.className = 'SaleMakerSelected';
          for (j=0; j<document.sale_form.length; j++) {
            if(document.sale_form.elements[j].type == 'checkbox') {
              relatedcheckbox = document.sale_form.elements[j];
              relatedrow = relatedcheckbox.parentNode.parentNode;
              if( (relatedcheckbox != currentcheckbox) && (relatedcheckbox.value.substr(0, currentcheckbox.value.length) == currentcheckbox.value) ) {
                if(!relatedcheckbox.disabled) {
<?php
    if ( (defined('AUTOCHECK')) && (AUTOCHECK == 'True') ) {
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
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetCategories();SetFocus();">
<div id="spiffycalendar" class="text"></div>
<?php
  } else {
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<?php
  }
?>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
    $form_action = 'insert';
    if ( ($action == 'edit') && ($_GET['sID']) ) {
      $form_action = 'update';

      $salemaker_sales = $db->Execute("select sale_id, sale_status, sale_name, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_categories_all, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " where sale_id = '" . (int)$_GET['sID'] . "'");

      $sInfo = new objectInfo($salemaker_sales->fields);
    } else {
      $sInfo = new objectInfo(array());
    }
?>
<script language="javascript">
var StartDate = new ctlSpiffyCalendarBox("StartDate", "sale_form", "start", "btnDate1","<?php echo (($sInfo->sale_date_start == '0001-01-01') ? '' : zen_date_short($sInfo->sale_date_start)); ?>",scBTNMODE_CUSTOMBLUE);
var EndDate = new ctlSpiffyCalendarBox("EndDate", "sale_form", "end", "btnDate2","<?php echo (($sInfo->sale_date_end == '0001-01-01') ? '' : zen_date_short($sInfo->sale_date_end)); ?>",scBTNMODE_CUSTOMBLUE);
</script>
      <tr><?php echo zen_draw_form("sale_form", FILENAME_SALEMAKER, zen_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action); ?><?php if ($form_action == 'update') echo zen_draw_hidden_field('sID', $_GET['sID']); ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_POPUP; ?></td>
            <td class="main" align="right" valign="top"><br><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . ($_GET['sID'] > 0 ? '&sID=' . $_GET['sID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_NAME; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('name', htmlspecialchars($sInfo->sale_name, ENT_COMPAT, CHARSET, TRUE), 'size="37"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_DEDUCTION; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('deduction', $sInfo->sale_deduction_value, 'size="8"') . TEXT_SALEMAKER_DEDUCTION_TYPE . zen_draw_pull_down_menu('type', $deduction_type_array, $sInfo->sale_deduction_type); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_PRICERANGE_FROM; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_input_field('from', $sInfo->sale_pricerange_from, 'size="8"') . TEXT_SALEMAKER_PRICERANGE_TO . zen_draw_input_field('to', $sInfo->sale_pricerange_to, 'size="8"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_SPECIALS_CONDITION; ?>&nbsp;</td>
            <td class="main"><?php echo zen_draw_pull_down_menu('condition', $specials_condition_array, $sInfo->sale_specials_condition); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_DATE_START; ?>&nbsp;</td>
            <td class="main"><script language="javascript">StartDate.writeControl(); StartDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SALEMAKER_DATE_END; ?>&nbsp;</td>
            <td class="main"><script language="javascript">EndDate.writeControl(); EndDate.dateFormat="<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script></td>
          </tr>
        </table>
      </tr>
<tr><table width="750" border="0" cellspacing="2" cellpadding="2">
<?php
    $categories_array = zen_get_category_tree('0','&nbsp;&nbsp;','0');
    $n = sizeof($categories_array);
    for($i = 0; $i < $n; $i++) {
      $parents = $db->Execute("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . $categories_array[$i]['id'] . "' ");
      $categories_array[$i]['parent_id'] = $parents->fields['parent_id'];
      $categories_array[$i]['categories_id'] = $categories_array[$i]['id'];
      $categories_array[$i]['path'] = $categories_array[$i]['categories_id'];
      $categories_array[$i]['indent'] = 0;
      $parent = $categories_array[$i]['parent_id'];
      while($parent != 0) {
        $categories_array[$i]['indent']++;
        for($j = 0; $j < $n; $j++) {
          if($categories_array[$j]['categories_id'] == $parent) {
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

  $prev_sales = $db->Execute("select sale_categories_all from " . TABLE_SALEMAKER_SALES);
  while (!$prev_sales->EOF) {
    $prev_categories = explode(',', $prev_sales->fields['sale_categories_all']);
    while(list($key,$value) = each($prev_categories)) {
      if ($value) $prev_categories_array[$value]++;
    }
    $prev_sales->MoveNext();
  }

// set Entire Catalog when set
  if (empty($sInfo->sale_categories_selected) AND !empty($sInfo->sale_categories_all)) {
    $zc_check_all_cats = 1;
  } else {
    $zc_check_all_cats = 0;
  }

    echo "      <tr>\n";
    echo '        <td valign="bottom" class="main">' . zen_draw_separator('pixel_trans.gif', '4', '1') . zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif') . "</td>\n";
    echo '        <td class="main" colspan="2"><br>' . TEXT_SALEMAKER_ENTIRE_CATALOG . "</td>\n";
    echo "      </tr>\n";
    echo '      <tr onClick="RowClick(\'0\')">' . "\n";
    echo '        <td width="10" class="main">' . zen_draw_checkbox_field('categories[]', '0', $zc_check_all_cats) . "</td>\n";
    echo '        <td class="main" colspan="2">' . TEXT_SALEMAKER_TOP . "</td>\n";
    echo "      </tr>\n";
    echo "      <tr>\n";
    echo '        <td valign="bottom" class="main">' . zen_draw_separator('pixel_trans.gif', '4', '1') . zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif') . "</td>\n";
    echo '        <td class="main" colspan="2"><br>' . TEXT_SALEMAKER_CATEGORIES . "</td>\n";
    echo "      </tr>\n";
    echo "      </table></tr>\n";
    echo '      <tr valign="top"><table width="80%" border ="0" cellspacing="2" cellpadding="2">' . "\n";
    foreach($categories_array as $category) {
      if (zen_not_null($sInfo->sale_categories_selected)) {
        $selected = in_array($category['categories_id'], $categories_selected);
      } else {
        $selected = false;
      }
      echo '        <tr valign="top"><td><table border="0" cellspacing="2" cellpadding="2">' . "\n";
      echo '      <tr onClick="RowClick(\'' . $category['path'] . '\')">' . "\n";
      echo '        <td width="10">' . zen_draw_checkbox_field('categories[]', $category['path'], $selected) . "</td>\n";
      echo '        <td width="40%">' . $category['text']. "</td>\n";
      echo '<td width="70%">';
    if ($prev_categories_array[$category['categories_id']]) {
      echo sprintf(TEXT_WARNING_SALEMAKER_PREVIOUS_CATEGORIES, $prev_categories_array[$category['categories_id']]);
    }
    echo "</td>\n";
    echo '      </tr>' . "\n";

    echo '        </table></td>' . "\n";
    echo '        <td align="right"><table border="0" cellspacing="2" cellpadding="2">' . "\n";
    echo '        <tr>' . "\n";
    if ($prev_categories_array[$category['categories_id']]) {
      echo '        <td>' . "\n";
?>
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_SALEMAKER_POPUP, 'cid=' . $category['categories_id']) . '\\\')">'.TEXT_MORE_INFO.'</a>'; ?>');
//--></script>
<?php
    }
    echo "</td>\n";
    echo '        </tr>' . "\n";
    echo '        </table></td>' . "\n";
    echo '        </tr>' . "\n";
  }
  echo '        </table></tr>' . "\n";
?>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_SALE_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center" colspan="2"><?php echo TABLE_HEADING_SALE_DEDUCTION; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SALE_DATE_START; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SALE_DATE_END; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $salemaker_sales_query_raw = "select sale_id, sale_status, sale_name, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_categories_all, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " order by sale_name";
    $salemaker_sales_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $salemaker_sales_query_raw, $salemaker_sales_query_numrows);
    $salemaker_sales = $db->Execute($salemaker_sales_query_raw);
    while (!$salemaker_sales->EOF) {
      if ((!isset($_GET['sID']) || (isset($_GET['sID']) && ($_GET['sID'] == $salemaker_sales->fields['sale_id']))) && !isset($sInfo)) {
        $sInfo_array = $salemaker_sales->fields;
        $sInfo = new objectInfo($sInfo_array);
      }

      if (isset($sInfo) && is_object($sInfo) && ($salemaker_sales->fields['sale_id'] == $sInfo->sale_id)) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $salemaker_sales->fields['sale_id']) . '\'">' . "\n";
      }
?>
                <td  class="dataTableContent" align="left"><?php echo $salemaker_sales->fields['sale_name']; ?></td>
                <td  class="dataTableContent" align="right"><?php echo $salemaker_sales->fields['sale_deduction_value']; ?></td>
                <td  class="dataTableContent" align="left"><?php echo $deduction_type_array[$salemaker_sales->fields['sale_deduction_type']]['text']; ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($salemaker_sales->fields['sale_date_start'] == '0001-01-01') ? TEXT_SALEMAKER_IMMEDIATELY : zen_date_short($salemaker_sales->fields['sale_date_start'])); ?></td>
                <td  class="dataTableContent" align="center"><?php echo (($salemaker_sales->fields['sale_date_end'] == '0001-01-01') ? TEXT_SALEMAKER_NEVER : zen_date_short($salemaker_sales->fields['sale_date_end'])); ?></td>
                <td  class="dataTableContent" align="center">
<?php
      if ($salemaker_sales->fields['sale_status'] == '1') {
        echo zen_draw_form('setflag_products', FILENAME_SALEMAKER, 'action=setflag&sID=' . $salemaker_sales->fields['sale_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
        <input type="hidden" name="flag" value="0" />
        </form>
<?php
      } else {
        echo zen_draw_form('setflag_products', FILENAME_SALEMAKER, 'action=setflag&sID=' . $salemaker_sales->fields['sale_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
        <input type="hidden" name="flag" value="1" />
        </form>
<?php
      }
?>
                </td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($salemaker_sales->fields['sale_id'] == $sInfo->sale_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $salemaker_sales->fields['sale_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
      $salemaker_sales->MoveNext();
    }
?>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $salemaker_sales_split->display_count($salemaker_sales_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SALES); ?></td>
                    <td class="smallText" align="right"><?php echo $salemaker_sales_split->display_links($salemaker_sales_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_sale.gif', IMAGE_NEW_SALE) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'copy':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_SALE . '</b>');

      $contents = array('form' => zen_draw_form('sales', FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=copyconfirm'));
      $contents[] = array('text' => sprintf(TEXT_INFO_COPY_INTRO, $sInfo->sale_name));
      $contents[] = array('text' => '<br>&nbsp;' . zen_draw_input_field('newname', htmlspecialchars($sInfo->sale_name . '_', ENT_COMPAT, CHARSET, TRUE), 'size="31"'));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_copy.gif', IMAGE_COPY) . '&nbsp;<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SALE . '</b>');

      $contents = array('form' => zen_draw_form('sales', FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('sID', $sInfo->sale_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $sInfo->sale_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->sale_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=copy') . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a> <a href="' . zen_href_link(FILENAME_SALEMAKER, 'page=' . $_GET['page'] . '&sID=' . $sInfo->sale_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
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
  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
  }
}
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>