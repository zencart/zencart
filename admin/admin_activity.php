<?php
/**
 * Admin Activity Log Viewer/Archiver
 *
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_activity.php 19538 2011-09-20 17:27:38Z drbyte $
 *
 * @TODO: prettify so on-screen output is more friendly, perhaps adding pagination support etc (using existing "s" and "p" params)
 * @TODO: prettify by hiding postdata until requested, either with hidden layers or other means
 * @TODO: Consider streaming to file line-by-line as an alternate output method in case of RAM blowout with large data quantities or low RAM config on servers.
 */
require ('includes/application_top.php');


// change destination here for path when using "save to file on server"
if (! defined('DIR_FS_ADMIN_ACTIVITY_EXPORT')) define('DIR_FS_ADMIN_ACTIVITY_EXPORT', DIR_FS_ADMIN . 'backups/');

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
$file = (isset($_POST['filename']) ? preg_replace('/[^\w\.-]/', '', $_POST['filename']) : 'admin_activity_archive_' . date('Y-m-d_H-i-s') . '.csv');

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
        $sort = ' DESC ';
      }
      $sql = "select a.access_date, a.admin_id, u.admin_name, a.ip_address, a.page_accessed, a.page_parameters, a.gzpost, a.flagged, a.attention
              FROM " . TABLE_ADMIN_ACTIVITY_LOG . " a LEFT OUTER JOIN " . TABLE_ADMIN . " u ON a.admin_id = u.admin_id ORDER BY access_date " . $sort . $limit;
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
          $exporter_output .= $FIELDSTART . "timestamp" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "admin_user" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "ip_address" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "page_accessed" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "parameters" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "flagged" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "attention" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "postdata" . $FIELDEND;
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
          $postoutput = '';
          if ($format == "XML")
          {
            $postoutput = nl2br(print_r(json_decode(@gzinflate($result->fields['gzpost'])), true));
            $exporter_output .= "<admin_activity_log>\n";
            $exporter_output .= "  <row>\n";
            $exporter_output .= "    <access_date>" . $result->fields['access_date'] . "</access_date>\n";
            $exporter_output .= "    <admin_id>" . $result->fields['admin_id'] . "</admin_id>\n";
            $exporter_output .= "    <admin_name>" . htmlspecialchars($result->fields['admin_name']) . "</admin_name>\n";
            $exporter_output .= "    <ip_address>" . $result->fields['ip_address'] . "</ip_address>\n";
            $exporter_output .= "    <page_accessed>" . $result->fields['page_accessed'] . "</page_accessed>\n";
            $exporter_output .= "    <page_parameters>" . htmlspecialchars($result->fields['page_parameters']) . "</page_parameters>\n";
            $exporter_output .= "    <flagged>" . htmlspecialchars($result->fields['flagged']) . "</flagged>\n";
            $exporter_output .= "    <attention>" . htmlspecialchars($result->fields['attention']) . "</attention>\n";
            $exporter_output .= "    <postdata>" . $postoutput . "</postdata>\n";
            $exporter_output .= "  </row>\n";
          } else
          { // output non-XML data-format
            $postoutput = print_r(json_decode(@gzinflate($result->fields['gzpost'])), true);
            if ($format == 'HTML') {
              $postoutput = nl2br(zen_output_string_protected($postoutput));
            } else {
              $postoutput = nl2br($postoutput);
            }
            $exporter_output .= $LINESTART;
            $exporter_output .= $FIELDSTART . $result->fields['access_date'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['admin_id'] . ' ' . $result->fields['admin_name'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['ip_address'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['page_accessed'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['page_parameters'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['flagged'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result->fields['attention'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $postoutput . $FIELDEND;
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
          $exporter_output .= "</admin_activity_log>\n";
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
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
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
          $f = fopen(DIR_FS_ADMIN_ACTIVITY_EXPORT . $file, 'w');
          if ($f) {
            fwrite($f, $exporter_output);
            fclose($f);
            //open output file for readback
            $readback = file_get_contents(DIR_FS_ADMIN_ACTIVITY_EXPORT . $file);
          }
          if ($readback !== FALSE && $readback == $exporter_output) {
            $messageStack->add_session(SUCCESS_EXPORT_ADMIN_ACTIVITY_LOG . $file, 'success');
          } else {
            $messageStack->add_session(FAILURE_EXPORT_ADMIN_ACTIVITY_LOG . $file, 'error');
          }
          unset($f);
        } // endif $save_to_file
      } //end if $records for processing not 0
      zen_redirect(zen_href_link(FILENAME_ADMIN_ACTIVITY));
      break;

// clean out the admin_activity_log
    case 'clean_admin_activity_log':
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes')
      {
        $db->Execute("truncate table " . TABLE_ADMIN_ACTIVITY_LOG);
        $admname = '{' . preg_replace('/[^\w]/', '*', zen_get_admin_name()) . '[' . (int)$_SESSION['admin_id'] . ']}';
        $sql_data_array = array( 'access_date' => 'now()',
                                 'admin_id' => (isset($_SESSION['admin_id'])) ? (int)$_SESSION['admin_id'] : 0,
                                 'page_accessed' =>  'Log reset by ' . $admname . '.',
                                 'page_parameters' => '',
                                 'ip_address' => substr($_SERVER['REMOTE_ADDR'],0,15)
                                 );
        zen_db_perform(TABLE_ADMIN_ACTIVITY_LOG, $sql_data_array);
        $messageStack->add_session(SUCCESS_CLEAN_ADMIN_ACTIVITY_LOG, 'success');
        unset($_SESSION['reset_admin_activity_log']);
        zen_redirect(zen_href_link(FILENAME_ADMIN_ACTIVITY));
      } else {
        $confirmation_needed = TRUE;
      }
    break;

  } //end switch / case
} //endif $action
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php
echo HTML_PARAMS;
?>>
<head>
<meta http-equiv="Content-Type"	content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
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
			<tr><?php echo zen_draw_form('export', FILENAME_ADMIN_ACTIVITY, 'action=save', 'post'); //, 'onsubmit="return check_form(export);"');   ?>
        <td align="center">
				<table border="0" cellspacing="0" cellpadding="2">
  			<tr><td><h2><?php echo HEADING_SUB1; ?></h2></td></tr>
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
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TEXT_ACTIVITY_EXPORT_DEST; ?></strong> <em><?php echo DIR_FS_ADMIN_ACTIVITY_EXPORT; ?></em>
              </td>
					</tr>
					<tr>
						<td class="main" align="right"><?php echo zen_image_submit('button_go.gif', IMAGE_GO) . '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ADMIN_ACTIVITY) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
					</tr>
				</table>
				</td>
				</form>
			</tr>

<!-- bof: reset admin_activity_log -->
      <tr>
        <td align="center"><table border="0" cellspacing="0" cellpadding="2">
      <tr><td><h2><?php echo HEADING_SUB2; ?></h2></td></tr>
          <tr>
            <td class=<?php echo ($_SESSION['reset_admin_activity_log'] == true ? "alert" : "main"); ?> align="left" valign="top"><?php echo TEXT_INFO_ADMIN_ACTIVITY_LOG; ?></td>
            <td class="main" align="right" valign="middle"><?php echo '<a href="' . zen_href_link(FILENAME_ADMIN_ACTIVITY, 'action=clean_admin_activity_log') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
<!-- eof: reset admin_activity_log -->

<?php } elseif ($confirmation_needed) { ?>
  <tr><td><?php echo TEXT_ADMIN_LOG_PLEASE_CONFIRM_ERASE; ?><?php echo zen_draw_form('admin_activity_erase', FILENAME_ADMIN_ACTIVITY, 'action=clean_admin_activity_log'); echo zen_image_submit('button_reset.gif', IMAGE_RESET); ?><input type="hidden" name="confirm" value="yes" /></form></td></tr>

<?php } ?>
				<!-- body_text_eof //-->
		</table>
		<!-- body_eof //--> <!-- footer //-->
<?php require (DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //--> <br />

</body>
</html>
<?php require (DIR_WS_INCLUDES . 'application_bottom.php'); ?>