<?php
/**
 * Coupon Exporter
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 *
 */
require ('includes/application_top.php');

// change destination here for path when using "save to file on server"
if (!defined('DIR_FS_COUPON_EXPORT')) define('DIR_FS_COUPON_EXPORT', DIR_FS_ADMIN . 'backups/');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$start = (isset($_GET['s']) ? (int)$_GET['s'] : 0);
$perpage = (isset($_GET['p']) ? (int)$_GET['p'] : 50);
$available_export_formats[0] = array('id' => '0' , 'text' => 'Export as HTML (ideal for on-screen viewing)', 'format' => 'HTML'); // review on screen
$available_export_formats[1] = array('id' => '1' , 'text' => 'Export to CSV (ideal for importing to spreadsheets)', 'format' => 'CSV'); // export to CSV
//  $available_export_formats[2]=array('id' => '2', 'text' => 'Export to TXT', 'format' => 'TXT');
//  $available_export_formats[3]=array('id' => '3', 'text' => 'Export to XML', 'format' => 'XML');
$save_to_file_checked = (isset($_POST['savetofile']) && zen_not_null($_POST['savetofile']) ? $_POST['savetofile'] : 0);
$post_format = (isset($_POST['format']) && zen_not_null($_POST['format']) ? $_POST['format'] : 1);
$format = $available_export_formats[$post_format]['format'];
$file = (isset($_POST['filename']) ? preg_replace('/[^\w\.\-]/', '', $_POST['filename']) : 'coupon_export_' . date('Y-m-d_H-i-s') . '.csv');
if (!preg_match('/.*\.(csv|txt|html?|xml)$/', $file)) $file .= '.txt';
if (isset($_GET['codebase'])) $_GET['codebase'] = preg_replace('/[^A-Za-z0-9\-\][\^!@#$%&*)(+=}{]/', '', $_GET['codebase']);

zen_set_time_limit(600);

if ($action != '')
{
  $NL = "\n";
  $limit = '';
  if ($perpage > 0 || $start > 0)
  {
    $limit = ' LIMIT ';
    if ($start > 0) $limit .= (int)$start;
    if ($start > 0 && $perpage > 0) $limit .= ', ';
    if ($perpage > 0) $limit .= (int)$perpage;
  }
  $sort = '';

  switch ($action)
  {
    case 'save':
      global $db;
      if ($format == 'CSV')
      {
        $FIELDSTART = '"';
        $FIELDEND = '"';
        $FIELDSEPARATOR = ',';
        $LINESTART = '';
        $LINEBREAK = "\n";
        $sort = ' ASC ';
        $limit = '';
      }
      if ($format == 'TXT')
      {
        $FIELDSTART = '';
        $FIELDEND = '';
        $FIELDSEPARATOR = "\t";
        $LINESTART = '';
        $LINEBREAK = "\n";
        $sort = ' ASC ';
      }
      if ($format == 'HTML')
      {
        $FIELDSTART = '<td>';
        $FIELDEND = '</td>';
        $FIELDSEPARATOR = "";
        $LINESTART = "<tr>";
        $LINEBREAK = "</tr>" . $NL;
        $sort = ' ASC ';
      }

      $sql = "SELECT c.coupon_id, coupon_code, coupon_amount, coupon_type, coupon_minimum_order, coupon_start_date, coupon_expire_date,
                 uses_per_coupon, uses_per_user, coupon_zone_restriction, coupon_active, coupon_calc_base, coupon_order_limit,
                 coupon_is_valid_for_sales, coupon_product_count
                 , coupon_name, coupon_description
              FROM " . TABLE_COUPONS . " c 
              LEFT JOIN " . TABLE_COUPONS_DESCRIPTION . " cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = :language)
              WHERE c.coupon_code LIKE :search ORDER BY c.coupon_active, c.coupon_id " . $sort;
      $sql = $db->bindVars($sql, ':search', $_GET['codebase'] . '%', 'string');
      $sql = $db->bindVars($sql, ':language', $_SESSION['languages_id'], 'integer');
      $result = $db->Execute($sql);
      $records = $result->RecordCount();

      if ($records == 0)
      {
        $messageStack->add("No Records Found.", 'error');
      } else
      { //process records
        $i = 0;
        // make a <table> tag if HTML output
        if ($format == "HTML")
        {
          $exporter_output .= '<table border="1">' . $NL;
        }
        // add column headers if CSV or HTML format
        if ($format == "CSV" || $format == "HTML")
        {
          $exporter_output .= $LINESTART;
          $exporter_output .= $FIELDSTART . "coupon_id" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_code" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_amount" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_type" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_minimum_order" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_start_date" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_expire_date" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "uses_per_coupon" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "uses_per_user" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_zone_restriction" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_active" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_calc_base" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_order_limit" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_is_valid_for_sales" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_product_count" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_name" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "coupon_description" . $FIELDEND;
          $exporter_output .= $LINEBREAK;
        }
        // headers - XML
        if ($format == "XML")
        {
          $exporter_output .= '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
        }
        // output real data
        while (! $result->EOF)
        {
          $i ++;
          if ($format == "XML")
          {
            $exporter_output .= "<coupon_export_log>\n";
            $exporter_output .= "  <row>\n";
            $exporter_output .= "    <coupon_id>" . $result->fields['coupon_id'] . "</coupon_id>\n";
            $exporter_output .= "    <coupon_code>" . htmlspecialchars($result->fields['coupon_code'], ENT_COMPAT, CHARSET, TRUE) . "</coupon_code>\n";
            $exporter_output .= "    <coupon_amount>" . $result->fields['coupon_amount'] . "</coupon_amount>\n";
            $exporter_output .= "    <coupon_type>" . $result->fields['coupon_type'] . "</coupon_type>\n";
            $exporter_output .= "    <coupon_minimum_order>" . $result->fields['coupon_minimum_order'] . "</coupon_minimum_order>\n";
            $exporter_output .= "    <coupon_start_date>" . $result->fields['coupon_start_date'] . "</coupon_start_date>\n";
            $exporter_output .= "    <coupon_expire_date>" . $result->fields['coupon_expire_date'] . "</coupon_expire_date>\n";
            $exporter_output .= "    <uses_per_coupon>" . $result->fields['uses_per_coupon'] . "</uses_per_coupon>\n";
            $exporter_output .= "    <uses_per_user>" . $result->fields['uses_per_user'] . "</uses_per_user>\n";
            $exporter_output .= "    <coupon_zone_restriction>" . $result->fields['coupon_zone_restriction'] . "</coupon_zone_restriction>\n";
            $exporter_output .= "    <coupon_active>" . $result->fields['coupon_active'] . "</coupon_active>\n";
            $exporter_output .= "    <coupon_calc_base>" . $result->fields['coupon_calc_base'] . "</coupon_calc_base>\n";
            $exporter_output .= "    <coupon_order_limit>" . $result->fields['coupon_order_limit'] . "</coupon_order_limit>\n";
            $exporter_output .= "    <coupon_is_valid_for_sales>" . $result->fields['coupon_is_valid_for_sales'] . "</coupon_is_valid_for_sales>\n";
            $exporter_output .= "    <coupon_product_count>" . $result->fields['coupon_product_count'] . "</coupon_product_count>\n";
            $exporter_output .= "    <coupon_name>" . $result->fields['coupon_name'] . "</coupon_name>\n";
            $exporter_output .= "    <coupon_description>" . $result->fields['coupon_description'] . "</coupon_description>\n";
            $exporter_output .= "  </row>\n";
          } else
          { // output non-XML data-format
            $exporter_output .= $LINESTART;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_id'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . zen_output_string_protected($result->fields['coupon_code']) . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_amount'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_type'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_minimum_order'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_start_date'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_expire_date'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['uses_per_coupon'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['uses_per_user'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_zone_restriction'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_active'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_calc_base'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_order_limit'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_is_valid_for_sales'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['coupon_product_count'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . zen_output_string_protected($result->fields['coupon_name']) . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . zen_output_string_protected($result->fields['coupon_description']) . $FIELDEND;
            $exporter_output .= $LINEBREAK;
          }
          $result->MoveNext();
        }
        if ($format == "HTML")
        {
          $exporter_output .= $NL . "</table>";
        }
        if ($format == "XML")
        {
          $exporter_output .= "</coupon_export_log>\n";
        }
        // theoretically, $i should == $records at this point.
        // status message
        if ($format != "HTML") $messageStack->add($records . TEXT_PROCESSED, 'success');
        // begin streaming file contents
        if ($save_to_file_checked != 1)
        { // not saving to a file, so do regular output
          if ($format == "CSV" || $format == "TXT" || $format == "XML")
          {
            if ($format == "CSV" || $format == "TXT")
            {
              $content_type = 'text/x-csv';
            } elseif ($format == "XML")
            {
              $content_type = 'text/xml; charset=' . CHARSET;
            }
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']))
            {
              header('Content-Type: application/octetstream');
//              header('Content-Type: '.$content_type);
//              header('Content-Disposition: inline; filename="' . $file . '"');
              header('Content-Disposition: attachment; filename=' . $file);
              header("Expires: Mon, 26 Jul 2001 05:00:00 GMT");
              header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
              header("Cache-Control: must_revalidate, post-check=0, pre-check=0");
              header("Pragma: public");
              header("Cache-control: private");
            } else
            {
              header('Content-Type: application/x-octet-stream');
//              header('Content-Type: '.$content_type);
              header('Content-Disposition: attachment; filename=' . $file);
              header("Expires: Mon, 26 Jul 2001 05:00:00 GMT");
              header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
              header("Pragma: no-cache");
            }
            session_write_close();
            echo $exporter_output;
            exit();
          } else
          {
            // HTML
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta charset="<?php echo CHARSET; ?>">
<title><?php echo HEADING_TITLE; ?></title>
<link href="includes/template/css/stylesheet.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php
            echo $exporter_output;
?>
</body>
</html>
<?php
            exit();
          }
        } else
        { //write to file
          //open output file for writing
          $f = fopen(DIR_FS_COUPON_EXPORT . $file, 'w');
          if ($f) {
            fwrite($f, $exporter_output);
            fclose($f);
            //open output file for readback
            $readback = file_get_contents(DIR_FS_COUPON_EXPORT . $file);
          }
          if ($readback !== FALSE && $readback == $exporter_output) {
            $messageStack->add_session(SUCCESS_EXPORT_DISCOUNT_COUPON_LOG . $file, 'success');
          } else {
            $messageStack->add_session(FAILURE_EXPORT_DISCOUNT_COUPON_LOG . $file, 'error');
          }
          unset($f);
        } // endif $save_to_file
      } //end if $records for processing not 0
      zen_redirect(zen_href_link(FILENAME_COUPON_ADMIN_EXPORT));
      break;

  } //end switch / case
} //endif $action

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript">
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php
require (DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <!-- body_text //-->
    <td width="100%" valign="top">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%">
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table>
        </td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

<?php if ($action == '') { ?>
      <tr><?php echo zen_draw_form('export', FILENAME_COUPON_ADMIN_EXPORT, 'action=save&codebase=' . $_GET['codebase'], 'post'); //, 'onsubmit="return check_form(export);"');   ?>
        <td align="center">
        <table border="0" cellspacing="0" cellpadding="2">
        <tr><td><h2><?php echo HEADING_SUB1 . ' - ' . $_GET['codebase']; ?></h2></td></tr>
          <tr>
            <td class="main" colspan="2"><?php echo TEXT_INSTRUCTIONS; ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo TEXT_ACTIVITY_EXPORT_FORMAT; ?></strong><br /><?php echo zen_draw_pull_down_menu('format', $available_export_formats, $format); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><strong><?php echo TEXT_ACTIVITY_EXPORT_FILENAME; ?></strong><br /><?php echo zen_draw_input_field('filename', htmlspecialchars($file, ENT_COMPAT, CHARSET, TRUE), ' size="60"'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo zen_draw_checkbox_field('savetofile', '1', $save_to_file_checked); ?> <strong><?php echo TEXT_ACTIVITY_EXPORT_SAVETOFILE; ?></strong><br />
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TEXT_ACTIVITY_EXPORT_DEST; ?></strong> <em><?php echo DIR_FS_COUPON_EXPORT; ?></em>
              </td>
          </tr>
          <tr>
            <td class="main" align="right"><?php echo zen_image_submit('button_go.gif', IMAGE_GO) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $_GET['cid'] . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table>
        </td>
        </form>
      </tr>


<?php } ?>
        <!-- body_text_eof //-->
    </table>
    <!-- body_eof //--> <!-- footer //-->
<?php require (DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //--> <br />

</body>
</html>
<?php require (DIR_WS_INCLUDES . 'application_bottom.php'); ?>
