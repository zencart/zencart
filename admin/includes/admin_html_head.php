<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<?php if (!defined('SKIP_CDN_JQUERY_LOADER') || SKIP_CDN_JQUERY_LOADER == FALSE) { ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<?php } ?>
<?php if (!defined('SKIP_CORE_JQUERY_LOADER') || SKIP_CORE_JQUERY_LOADER == FALSE) {
  //fall back to local if CDN copy didn't load or if offline
  $jqpath = DIR_FS_ADMIN . 'includes/javascript/jquery.min.js';
  $jquipath = DIR_FS_ADMIN . 'includes/javascript/jquery-ui.min.js';
  $jquicsspath = DIR_FS_ADMIN . 'includes/javascript/jquery-ui.min.css';
  if (file_exists($jqpath)) { ?>
    <script>window.jQuery || document.write('<script src="<?php echo $jqpath;?>"><\/script><script src="<?php echo $jquipath;?>"><\/script><link type="stylesheet" href="<?php echo $jquicsspath;?>" />');</script>
<?php } ?>
<?php } ?>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>

<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script type="text/javascript">
  <!--
  $(document).ready(function(){
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  });
  // -->
</script>
