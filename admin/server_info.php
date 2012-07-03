<?php
/**
 * @package admin
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: server_info.php 13982 2009-07-25 15:49:38Z drbyte $
 */

  require('includes/application_top.php');
  $version_check_sysinfo=true;

  $system = zen_get_system_information();

// the following is for display later
  $sinfo =  '<table width="700" border="1" cellpadding="3" style="border: 0px; border-color: #000000;">' .
         '  <tr align="center"><td><a href="http://www.zen-cart.com"><img border="0" src="images/logo.gif" alt=" Zen Cart " /></a>' .
         '     <h2 class="p"> ' . PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '</h2>' .
               ((PROJECT_VERSION_PATCH1 =='') ? '' : '<h3>Patch: ' . PROJECT_VERSION_PATCH1 . '::' . PROJECT_VERSION_PATCH1_SOURCE . '</h3>') .
               ((PROJECT_VERSION_PATCH2 =='') ? '' : '<h3>Patch: ' . PROJECT_VERSION_PATCH2 . '::' . PROJECT_VERSION_PATCH2_SOURCE . '</h3>') .
         '     <h2 class="p"> ' . PROJECT_DATABASE_LABEL . ' ' . PROJECT_DB_VERSION_MAJOR . '.' . PROJECT_DB_VERSION_MINOR . '</h2>' .
               ((PROJECT_DB_VERSION_PATCH1 =='') ? '' : '<h3>Patch: ' . PROJECT_DB_VERSION_PATCH1 . '::' . PROJECT_DB_VERSION_PATCH1_SOURCE . '</h3>') .
               ((PROJECT_DB_VERSION_PATCH2 =='') ? '' : '<h3>Patch: ' . PROJECT_DB_VERSION_PATCH2 . '::' . PROJECT_DB_VERSION_PATCH2_SOURCE . '</h3>') ;

  $hist_query = "SELECT * from " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Main' GROUP BY concat(project_version_major, project_version_minor, project_version_comment) ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC";
  $hist_details = $db->Execute($hist_query);
      $sinfo .=  'v' . $hist_details->fields['project_version_major'] . '.' . $hist_details->fields['project_version_minor'];
      if (zen_not_null($hist_details->fields['project_version_patch'])) $sinfo .= '&nbsp;&nbsp;Patch: ' . $hist_details->fields['project_version_patch'];
      if (zen_not_null($hist_details->fields['project_version_date_applied'])) $sinfo .= ' &nbsp;&nbsp;[' . $hist_details->fields['project_version_date_applied'] . '] ';
      if (zen_not_null($hist_details->fields['project_version_comment'])) $sinfo .= ' &nbsp;&nbsp;(' . $hist_details->fields['project_version_comment'] . ')';
      $sinfo .=  '<br />';
  $hist_query = "SELECT * from " . TABLE_PROJECT_VERSION_HISTORY . " WHERE project_version_key = 'Zen-Cart Main' GROUP BY concat(project_version_major, project_version_minor, project_version_comment) ORDER BY project_version_date_applied DESC, project_version_major DESC, project_version_minor DESC, project_version_patch DESC";
  $hist_details = $db->Execute($hist_query);
    while (!$hist_details->EOF) {
      $sinfo .=  'v' . $hist_details->fields['project_version_major'] . '.' . $hist_details->fields['project_version_minor'];
      if (zen_not_null($hist_details->fields['project_version_patch'])) $sinfo .= '&nbsp;&nbsp;Patch: ' . $hist_details->fields['project_version_patch'];
      if (zen_not_null($hist_details->fields['project_version_date_applied'])) $sinfo .= ' &nbsp;&nbsp;[' . $hist_details->fields['project_version_date_applied'] . '] ';
      if (zen_not_null($hist_details->fields['project_version_comment'])) $sinfo .= ' &nbsp;&nbsp;(' . $hist_details->fields['project_version_comment'] . ')';
      $sinfo .=  '<br />';
      $hist_details->MoveNext();
    }
  $sinfo .= '</td>' .
         '  </tr>' .
         '</table>';
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
</head>
<body onLoad="init()" >
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<!-- body_text //-->
<table width="90%" border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td colspan="2" height="44"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_SERVER_HOST; ?></strong> <?php echo $system['host'] . ' (' . $system['ip'] . ')'; ?>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> </strong></td>
    <td width="51%"><strong><?php echo TITLE_DATABASE_HOST; ?></strong> <?php echo $system['db_server'] . ' (' . $system['db_ip'] . ')'; ?></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_SERVER_OS; ?></strong> <?php echo $system['system'] . ' ' . $system['kernel']; ?> &nbsp;&nbsp;</td>
    <td width="51%"><strong><?php echo TITLE_DATABASE; ?></strong> <?php echo $system['db_version'] . ($system['mysql_strict_mode'] == true ? '<em> ' . TITLE_MYSQL_STRICT_MODE . '</em>' : ''); ?></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_SERVER_DATE; ?></strong> <?php echo $system['date']; ?> &nbsp;</td>
    <td width="51%"><strong><?php echo TITLE_DATABASE_DATE; ?></strong> <?php echo $system['db_date']; ?> </td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_SERVER_UP_TIME; ?></strong> <?php echo $system['uptime']; ?></td>
    <td width="51%"><strong><?php echo TITLE_HTTP_SERVER; ?></strong> <?php echo $system['http_server']; ?></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_PHP_VERSION; ?></strong> <?php echo $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')' . ($system['php_memlimit'] != '' ? ' &nbsp; <strong>' . TITLE_PHP_MEMORY_LIMIT . '</strong> ' . $system['php_memlimit'] : ''); ?></td>
    <td><?php echo '<strong>' . TITLE_PHP_SAFE_MODE . '</strong> ' . ($system['php_safemode'] != '' && $system['php_safemode'] != 'off' && $system['php_safemode'] != '0' ? 'On' : 'Off'); ?></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_PHP_FILE_UPLOADS; ?></strong>
      <?php echo ($system['php_file_uploads'] != '' && $system['php_file_uploads'] != 'off' && $system['php_file_uploads'] != '0') ? 'On' : 'Off';  echo ' &nbsp;&nbsp; <strong>' . TITLE_PHP_UPLOAD_MAX . '</strong> ' . $system['php_uploadmaxsize'];?></td>
    <td><strong><?php echo TITLE_PHP_POST_MAX_SIZE; ?></strong> <?php echo $system['php_postmaxsize']; ?></td>
  </tr>
  <tr>
    <td><strong><?php echo TITLE_DATABASE_DATA_SIZE; ?></strong> <?php echo number_format(($system['database_size']/1024),0); ?> kB</td>
    <td><strong><?php echo TITLE_DATABASE_INDEX_SIZE; ?></strong> <?php echo number_format(($system['index_size']/1024),0); ?> kB</td>
  </tr>
</table>
<br />
<table border="0" cellspacing="0" cellpadding="4" width="90%">
  <tr>
    <td width="100%" height="23">
      <style type="text/css">
 table, td, tr {font-family: sans-serif; font-size: 11px;}
.p {text-align: center;}
.e {background-color: #ccccff; font-weight: bold; font-size: 11px;}
.h {background-color: #9999cc; font-weight: bold; font-size: 11px;}
.v {background-color: #cccccc; font-size: 12px;}
i {color: #666666; font-size: 11px;}
hr {display: none; font-size: 11px;}
</style>
<?php
  if (function_exists('ob_start')) {
    ob_start();
    phpinfo();
    $phpinfo = ob_get_contents();
    ob_end_clean();
    $regs = '';
    $phpinfo = str_replace('border: 1px', '', $phpinfo);
    $phpinfo = str_replace('width="600"', 'width="700"', $phpinfo);
    preg_match('/<body>(.*)<\/body>/msi', $phpinfo, $regs);
    echo $sinfo;
    echo $regs[1];
  } else {
    echo $sinfo;
    phpinfo();
  }
?>
    </td>
  </tr>
</table>
<!-- body_text_eof //-->

<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
