<?php
/**
 * Admin Activity Log Viewer/Archiver
 *
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 May 08 Modified in v1.5.6b $
 *
 * @TODO: prettify so on-screen output is more friendly, perhaps adding pagination support etc (using existing "s" and "p" params)
 * @TODO: prettify by hiding postdata until requested, either with hidden layers or other means
 * @TODO: Consider streaming to file line-by-line as an alternate output method in case of RAM blowout with large data quantities or low RAM config on servers.
 */
require ('includes/application_top.php');


// change destination here for path when using "save to file on server"
if (!defined('DIR_FS_ADMIN_ACTIVITY_EXPORT')) {
  define('DIR_FS_ADMIN_ACTIVITY_EXPORT', DIR_FS_ADMIN . 'backups/');
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$start = (isset($_GET['s']) ? (int)$_GET['s'] : 0);
$perpage = (isset($_GET['p']) ? (int)$_GET['p'] : 50);
$available_export_formats = array();
$available_export_formats[0] = array('id' => '0', 'text' => TEXT_EXPORTFORMAT0, 'format' => 'HTML'); // review on screen
$available_export_formats[1] = array('id' => '1', 'text' => TEXT_EXPORTFORMAT1, 'format' => 'CSV'); // export to CSV
//  $available_export_formats[2]=array('id' => '2', 'text' => TEXT_EXPORTFORMAT2, 'format' => 'TXT');
//  $available_export_formats[3]=array('id' => '3', 'text' => TEXT_EXPORTFORMAT3, 'format' => 'XML');
$save_to_file_checked = (isset($_POST['savetofile']) && zen_not_null($_POST['savetofile']) ? $_POST['savetofile'] : 0);
$post_format = (isset($_POST['format']) && zen_not_null($_POST['format']) ? $_POST['format'] : 1);
$format = $available_export_formats[$post_format]['format'];
$file = (isset($_POST['filename']) ? preg_replace('/[^\w\.-]/', '', $_POST['filename']) : 'admin_activity_archive_' . date('Y-m-d_H-i-s') . '.csv');
if (!preg_match('/.*\.(csv|txt|html?|xml)$/', $file)) {
  $file .= '.txt';
}
$filter_options = array();
$filter_options[0] = array('id' => '0', 'text' => TEXT_EXPORTFILTER0, 'filter' => 'all');
$filter_options[1] = array('id' => '1', 'text' => TEXT_EXPORTFILTER1, 'filter' => 'info');
$filter_options[2] = array('id' => '2', 'text' => TEXT_EXPORTFILTER2, 'filter' => 'notice');
$filter_options[3] = array('id' => '3', 'text' => TEXT_EXPORTFILTER3, 'filter' => 'warning');
$filter_options[4] = array('id' => '4', 'text' => TEXT_EXPORTFILTER4, 'filter' => 'notice+warning');
$post_filter = (isset($_POST['filter']) && (int)$_POST['filter'] >= 0 && (int)$_POST['filter'] < 5) ? (int)$_POST['filter'] : 4;
$selected_filter = $filter_options[$post_filter]['filter'];

zen_set_time_limit(600);

if ($action != '') {
  $NL = "\n";
  $limit = '';
  if ($perpage > 0 || $start > 0) {
    $limit = ' LIMIT ';
    if ($start > 0) {
      $limit .= (int)$start;
    }
    if ($start > 0 && $perpage > 0) {
      $limit .= ', ';
    }
    if ($perpage > 0) {
      $limit .= (int)$perpage;
    }
  }
  $sort = '';

  switch ($action) {
    case 'save':
      global $db;

      zen_record_admin_activity(sprintf(TEXT_ACTIVITY_LOG_ACCESSED, $format, $selected_filter, ($save_to_file_checked ? '(SaveToFile)' : ($format == 'HTML' ? '(Output to browser)' : '(Download to browser)'))), 'warning');

      if ($format == 'CSV') {
        $FIELDSTART = '"';
        $FIELDEND = '"';
        $FIELDSEPARATOR = ',';
        $LINESTART = '';
        $LINEBREAK = "\n";
        $sort = ' ASC ';
        $limit = '';
      }
      if ($format == 'TXT') {
        $FIELDSTART = '';
        $FIELDEND = '';
        $FIELDSEPARATOR = "\t";
        $LINESTART = '';
        $LINEBREAK = "\n";
        $sort = ' ASC ';
      }
      if ($format == 'HTML') {
        $FIELDSTART = '<td>';
        $FIELDEND = '</td>';
        $FIELDSEPARATOR = "";
        $LINESTART = "<tr>";
        $LINEBREAK = "</tr>" . $NL;
        $sort = ' DESC ';
      }

      $where = '';
      switch ($selected_filter) {
        case 'warning':
          $where = " severity='warning'";
          break;
        case 'notice+warning':
          $where = " severity in ('warning','notice')";
          break;
        case 'notice':
          $where = " severity='notice'";
          break;
        case 'info':
          $where = " severity='info'";
          break;
        default:
          $where = '';
      }
      if ($where != '') {
        $where = " WHERE " . $where;
      }

      $sql = "SELECT a.access_date, a.admin_id, u.admin_name, a.ip_address, a.page_accessed, a.page_parameters, a.gzpost, a.flagged, a.attention, a.severity, a.logmessage
              FROM " . TABLE_ADMIN_ACTIVITY_LOG . " a
              LEFT OUTER JOIN " . TABLE_ADMIN . " u ON a.admin_id = u.admin_id
              " . $where . "
              ORDER BY log_id " . $sort . $limit;
      $results = $db->Execute($sql);
      $records = $results->RecordCount();
      if ($records == 0) {

        $messageStack->add_session(TEXT_NO_RECORDS_FOUND, 'error');
      } else { //process records
        $i = 0;
        // make a <table> tag if HTML output
        if ($format == "HTML") {
          $exporter_output .= '<table border="1">' . $NL;
        }
        // add column headers if CSV or HTML format
        if ($format == "CSV" || $format == "HTML") {
          $exporter_output .= $LINESTART;
          $exporter_output .= $FIELDSTART . "severity" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "timestamp" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "ip_address" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "admin_user" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "page_accessed" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "parameters" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "flagged" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "attention" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "logmessage" . $FIELDEND;
          $exporter_output .= $FIELDSEPARATOR;
          $exporter_output .= $FIELDSTART . "postdata" . $FIELDEND;
          $exporter_output .= $LINEBREAK;
        }
        // headers - XML
        if ($format == "XML") {
          $exporter_output .= '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n";
        }
        // output real data
        foreach ($results as $result) {
          $i ++;
          $postoutput = '';
          if ($format == "XML") {
            $postoutput = nl2br(print_r(json_decode(@gzinflate($result['gzpost'])), true));
            $exporter_output .= "<admin_activity_log>\n";
            $exporter_output .= "  <row>\n";
            $exporter_output .= "    <severity>" . $result['severity'] . "</severity>\n";
            $exporter_output .= "    <access_date>" . $result['access_date'] . "</access_date>\n";
            $exporter_output .= "    <admin_id>" . $result['admin_id'] . "</admin_id>\n";
            $exporter_output .= "    <admin_name>" . htmlspecialchars($result['admin_name'], ENT_COMPAT, CHARSET, TRUE) . "</admin_name>\n";
            $exporter_output .= "    <ip_address>" . $result['ip_address'] . "</ip_address>\n";
            $exporter_output .= "    <page_accessed>" . $result['page_accessed'] . "</page_accessed>\n";
            $exporter_output .= "    <page_parameters>" . htmlspecialchars($result['page_parameters'], ENT_COMPAT, CHARSET, TRUE) . "</page_parameters>\n";
            $exporter_output .= "    <flagged>" . htmlspecialchars($result['flagged'], ENT_COMPAT, CHARSET, TRUE) . "</flagged>\n";
            $exporter_output .= "    <attention>" . htmlspecialchars($result['attention'], ENT_COMPAT, CHARSET, TRUE) . "</attention>\n";
            $exporter_output .= "    <logmessage>" . htmlspecialchars($result['logmessage'], ENT_COMPAT, CHARSET, TRUE) . "</logmessage>\n";
            $exporter_output .= "    <postdata>" . $postoutput . "</postdata>\n";
            $exporter_output .= "  </row>\n";
          } else { // output non-XML data-format
            $postoutput = print_r(json_decode(@gzinflate($result['gzpost'])), true);
            if ($format == 'HTML') {
              $postoutput = nl2br(zen_output_string_protected($postoutput));
            } else {
              $postoutput = nl2br($postoutput);
            }
            $exporter_output .= $LINESTART;
            $exporter_output .= $FIELDSTART . $result['severity'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['access_date'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['ip_address'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['admin_id'] . ' ' . $result['admin_name'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['page_accessed'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['page_parameters'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['flagged'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['attention'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $result['logmessage'] . $FIELDEND;
            $exporter_output .= $FIELDSEPARATOR;
            $exporter_output .= $FIELDSTART . $postoutput . $FIELDEND;
            $exporter_output .= $LINEBREAK;
          }
        }
        if ($format == "HTML") {
          $exporter_output .= $NL . "</table>";
        }
        if ($format == "XML") {
          $exporter_output .= "</admin_activity_log>\n";
        }
        // theoretically, $i should == $records at this point.
        // status message
        if ($format != "HTML")
          $messageStack->add($records . TEXT_PROCESSED, 'success');
        // begin streaming file contents
        if ($save_to_file_checked != 1) { // not saving to a file, so do regular output
          if ($format == "CSV" || $format == "TXT" || $format == "XML") {
            if ($format == "CSV" || $format == "TXT") {
              $content_type = 'text/x-csv';
            } elseif ($format == "XML") {
              $content_type = 'text/xml; charset=' . CHARSET;
            }
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
              header('Content-Type: application/octetstream');
//              header('Content-Type: '.$content_type);
//              header('Content-Disposition: inline; filename="' . $file . '"');
              header('Content-Disposition: attachment; filename=' . $file);
              header("Expires: Mon, 26 Jul 2001 05:00:00 GMT");
              header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
              header("Cache-Control: must_revalidate, post-check=0, pre-check=0");
              header("Pragma: public");
              header("Cache-control: private");
            } else {
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
          } else {
            // HTML
            ?>
            <!doctype html>
            <html <?php echo HTML_PARAMS; ?>>
              <head>
                <meta charset="<?php echo CHARSET; ?>">
                <title><?php echo TITLE; ?></title>
                <link rel="stylesheet" href="includes/stylesheet.css">
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
        } else { //write to file
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
      if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        $zco_notifier->notify('NOTIFY_ADMIN_ACTIVITY_LOG_RESET');
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
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php
    require (DIR_WS_INCLUDES . 'header.php');
    ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->

      <?php if ($action == '') { ?>
        <div class="row">
            <?php echo zen_draw_form('export', FILENAME_ADMIN_ACTIVITY, 'action=save', 'post', 'class="form-horizontal"'); //, 'onsubmit="return check_form(export);"');     ?>
          <h4><?php echo HEADING_SUB1; ?></h4>
            <div class="row"><?php echo TEXT_INSTRUCTIONS; ?></div>
            <div class="form-group"><?php echo zen_draw_label(TEXT_ACTIVITY_EXPORT_FILTER, 'filter', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_pull_down_menu('filter', $filter_options, $post_filter, 'class="form-control"'); ?>
              </div>
            </div>
            <div class="form-group"><?php echo zen_draw_label(TEXT_ACTIVITY_EXPORT_FORMAT, 'format', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_pull_down_menu('format', $available_export_formats, $format, 'class="form-control"'); ?>
              </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(TEXT_ACTIVITY_EXPORT_FILENAME, 'filename', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9 col-md-6">
                  <?php echo zen_draw_input_field('filename', htmlspecialchars($file, ENT_COMPAT, CHARSET, TRUE), 'class="form-control" size="60"'); ?>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-offset-3 col-sm-9 col-md-6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('savetofile', '1', $save_to_file_checked); ?><?php echo TEXT_ACTIVITY_EXPORT_SAVETOFILE; ?></label>
                </div>
                <div><strong><?php echo TEXT_ACTIVITY_EXPORT_DEST; ?></strong> <em><?php echo DIR_FS_ADMIN_ACTIVITY_EXPORT; ?></em></div>
              </div>
            </div>
            <div class="text-right">
              <button class="btn btn-primary"><?php echo IMAGE_GO; ?></button> <a href="<?php echo zen_href_link(FILENAME_ADMIN_ACTIVITY); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a></div>
            <div class="row"><?php echo TEXT_INTERPRETING_LOG_DATA; ?></div>
            <?php echo '</form>'; ?>
        </div>

        <!-- bof: reset admin_activity_log -->
        <h4><?php echo HEADING_SUB2; ?></h4>
        <div class="row">
          <div class="col <?php echo (!empty($_SESSION['reset_admin_activity_log']) ? "text-danger" : "main"); ?>"><?php echo TEXT_INFO_ADMIN_ACTIVITY_LOG; ?></div>
          <div class="text-right"><a href="<?php echo zen_href_link(FILENAME_ADMIN_ACTIVITY, 'action=clean_admin_activity_log'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_RESET; ?></a></div>
        </div>
        <!-- eof: reset admin_activity_log -->

      <?php } elseif ($confirmation_needed) { ?>
        <div class="row">
            <?php echo TEXT_ADMIN_LOG_PLEASE_CONFIRM_ERASE; ?>
            <?php echo zen_draw_form('admin_activity_erase', FILENAME_ADMIN_ACTIVITY, 'action=clean_admin_activity_log'); ?>
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_RESET; ?></button>
          <?php echo zen_draw_hidden_field('confirm', 'yes'); ?>
          <?php echo '</form>'; ?>
        </div>

      <?php } ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //--> <!-- footer //-->
    <?php require (DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //--> <br />

  </body>
</html>
<?php require (DIR_WS_INCLUDES . 'application_bottom.php'); ?>
