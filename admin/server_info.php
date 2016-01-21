<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: server_info.php   Modified in v1.5.5 $
 */

  require('includes/application_top.php');
  $version_check_sysinfo = true;

  $system = zen_get_system_information();

// the following is for display later
  $sinfo =  '<div class="sysinfo wrapper">' .
         '  <div class="center"><a href="http://www.zen-cart.com"><img border="0" src="images/small_zen_logo.gif" alt=" Zen Cart " /></a></div>' .
         '  <div class="center"><h2> ' . PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '</h2>' .
               ((PROJECT_VERSION_PATCH1 =='') ? '' : '<h3>Patch: ' . PROJECT_VERSION_PATCH1 . '::' . PROJECT_VERSION_PATCH1_SOURCE . '</h3>') .
               ((PROJECT_VERSION_PATCH2 =='') ? '' : '<h3>Patch: ' . PROJECT_VERSION_PATCH2 . '::' . PROJECT_VERSION_PATCH2_SOURCE . '</h3>') .
         '     <h2> ' . PROJECT_DATABASE_LABEL . ' ' . PROJECT_DB_VERSION_MAJOR . '.' . PROJECT_DB_VERSION_MINOR . '</h2>' .
               ((PROJECT_DB_VERSION_PATCH1 =='') ? '' : '<h3>Patch: ' . PROJECT_DB_VERSION_PATCH1 . '::' . PROJECT_DB_VERSION_PATCH1_SOURCE . '</h3>') .
               ((PROJECT_DB_VERSION_PATCH2 =='') ? '' : '<h3>Patch: ' . PROJECT_DB_VERSION_PATCH2 . '::' . PROJECT_DB_VERSION_PATCH2_SOURCE . '</h3>') ;
  $sinfo .= '  </div><div class="center">';
  $hist_query = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC";
  $hist_details = $db->Execute($hist_query);
      $sinfo .=  'v' . $hist_details->fields['project_version_major'] . '.' . $hist_details->fields['project_version_minor'];
      if (zen_not_null($hist_details->fields['project_version_patch'])) $sinfo .= '&nbsp;&nbsp;Patch: ' . $hist_details->fields['project_version_patch'];
      if (zen_not_null($hist_details->fields['project_version_date_applied'])) $sinfo .= ' &nbsp;&nbsp;[' . $hist_details->fields['project_version_date_applied'] . '] ';
      if (zen_not_null($hist_details->fields['project_version_comment'])) $sinfo .= ' &nbsp;&nbsp;(' . $hist_details->fields['project_version_comment'] . ')';
      $sinfo .=  '<br />';
  $hist_query = "SELECT * from " . TABLE_PROJECT_VERSION_HISTORY . " WHERE project_version_key = 'Zen-Cart Main' ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC, project_version_patch DESC";
  $hist_details = $db->Execute($hist_query);
    while (!$hist_details->EOF) {
      $sinfo .=  'v' . $hist_details->fields['project_version_major'] . '.' . $hist_details->fields['project_version_minor'];
      if (zen_not_null($hist_details->fields['project_version_patch'])) $sinfo .= '&nbsp;&nbsp;Patch: ' . $hist_details->fields['project_version_patch'];
      if (zen_not_null($hist_details->fields['project_version_date_applied'])) $sinfo .= ' &nbsp;&nbsp;[' . $hist_details->fields['project_version_date_applied'] . '] ';
      if (zen_not_null($hist_details->fields['project_version_comment'])) $sinfo .= ' &nbsp;&nbsp;(' . $hist_details->fields['project_version_comment'] . ')';
      $sinfo .=  '<br />';
      $hist_details->MoveNext();
    }
  $sinfo .= '</div></div>';
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
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
<style>
.pageHeading {font-size: 2em;}
.serverInfo{max-width: 800px; margin-left: auto; margin-right: auto; font-size: 1.1em;}
.infocell {float:left; width: 380px;}
.sysinfo {max-width:700px; margin: auto;border: 2px solid black;padding:1.5em;margin-top:2em;}
.clearBoth{clear:both}

.phpinfo table{border:none;padding:0;margin:0; border-spacing:0;border-collapse:collapse;}
.phpinfo table {width: 768px !important;}
.phpinfo {margin:auto;}
.phpinfo hr {display: none;}
.phpinfo DIV {background-color: #fff; color: #222; font-family: sans-serif;}
pre {margin: 0; font-family: monospace;}
.phpinfo table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}
.center {text-align: center;}
.center table {margin: 1em auto; text-align: left;}
.center th {text-align: center !important;}

.phpinfo h1, .phpinfo h1 a {font-size: 150%;}
.phpinfo h2, .phpinfo h2 a {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccf; width: 300px; font-weight: bold;}
.h {background-color: #99c; font-weight: bold;}
.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
.v i {color: #999;}
.phpinfo img {float: right; border: 0;}
</style>
</head>
<body onLoad="init()" class="sysinfoBody">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<!-- body_text //-->
<h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
<div class="serverInfo">
      <div class="infocell"><strong><?php echo TITLE_SERVER_HOST; ?></strong> <?php echo $system['host'] . ' (' . $system['ip'] . ')'; ?></div>
      <div class="infocell"><strong><?php echo TITLE_SERVER_OS; ?></strong> <?php echo $system['system'] . ' ' . $system['kernel']; ?> </div>
      <div class="infocell"><strong><?php echo TITLE_SERVER_DATE; ?></strong> <?php echo $system['date']; ?> &nbsp;</div>
      <div class="infocell"><strong><?php echo TITLE_SERVER_UP_TIME; ?></strong> <?php echo $system['uptime']; ?></div>
      <div class="infocell"><strong><?php echo TITLE_HTTP_SERVER; ?></strong> <?php echo $system['http_server']; ?></div>
      <div class="infocell"><strong><?php echo TITLE_PHP_VERSION; ?></strong> <?php echo $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')'; ?></div>
      <div class="infocell"><strong><?php echo TITLE_PHP_FILE_UPLOADS; ?></strong> <?php echo ($system['php_file_uploads'] != '' && $system['php_file_uploads'] != 'off' && $system['php_file_uploads'] != '0') ? 'On' : 'Off';?></div>
      <div class="infocell"><strong><?php echo TITLE_PHP_UPLOAD_MAX;?></strong> <?php echo $system['php_uploadmaxsize'];?></div>
      <?php echo ($system['php_memlimit'] != '' ? '<div class="infocell"><strong>' . TITLE_PHP_MEMORY_LIMIT . '</strong> ' . $system['php_memlimit'] . '</div>' : ''); ?>
      <div class="infocell"><strong><?php echo TITLE_PHP_POST_MAX_SIZE; ?></strong> <?php echo $system['php_postmaxsize']; ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE; ?></strong> <?php echo $system['db_version'] . ($system['mysql_strict_mode'] == true ? '<em> ' . TITLE_MYSQL_STRICT_MODE . '</em>' : ''); ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_HOST; ?></strong> <?php echo $system['db_server'] . ' (' . $system['db_ip'] . ')'; ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_DATE; ?></strong> <?php echo $system['db_date']; ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_DATA_SIZE; ?></strong> <?php echo number_format(($system['database_size']/1024),0); ?> kB</div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_INDEX_SIZE; ?></strong> <?php echo number_format(($system['index_size']/1024),0); ?> kB</div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_MYSQL_SLOW_LOG_STATUS; ?></strong> <?php echo $system['mysql_slow_query_log_status'] != '0' ? 'On' : 'Off'; ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_MYSQL_SLOW_LOG_FILE; ?></strong> <?php echo zen_output_string_protected($system['mysql_slow_query_log_file']); ?></div>
      <div class="infocell"><strong><?php echo TITLE_DATABASE_MYSQL_MODE; ?></strong> <?php echo $system['mysql_mode'] == '' ? '(None set)' : zen_output_string_protected(str_replace(',', ', ', $system['mysql_mode'])); ?></div>
</div>
<br class="clearBoth">
<?php echo $sinfo; ?>
<br>
<div class="phpinfo">
<?php
  if (function_exists('ob_start')) {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();
    $regs = '';
    preg_match('/<body>(.*)<\/body>/msi', $phpinfo, $regs);
    echo $regs[1];
  } else {
    phpinfo();
  }
?>
</div>
<!-- body_text_eof //-->

<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
