<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_html_head.php $
 */
?>
<!doctype html>
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php echo HTML_PARAMS; ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php echo HTML_PARAMS; ?>> <!--<![endif]-->
<head>
<meta charset="<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

<link rel="stylesheet" type="text/css" href="includes/template/css/normalize.css" id="normalizeCSS">
<!-- <link rel="stylesheet" type="text/css" href="includes/template/css/foundation.min.css" id="foundationCSS"> -->
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet.css" id="stylesheetCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet_print.css" media="print" id="printCSS">

<?php if (isset($extraCss)) { ?>
  <?php foreach ($extraCss as $css) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $css['location']; ?>">
  <?php } ?>
<?php } ?>

<script src="includes/template/javascript/foundation/custom.modernizr.js"></script>

<?php /** CDN for jQuery core **/ ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="includes/template/javascript/foundation/jquery.min.js"><\/script>');</script>

<?php /** Load Foundation framework core**/ ?>
<script src="includes/template/javascript/foundation/foundation.min.js"></script>

<script src="includes/template/javascript/jquery-validation/jquery.validate.js"></script>
<?php if (file_exists(DIR_FS_ADMIN . 'includes/template/javascript/jquery-validation/localization/messages_' . substr($_SESSION['languages_code'], 0, strpos($_SESSION['languages_code'], '_')) . '.js')) { ?>
<script src="<?php echo 'includes/template/javascript/jquery-validation/localization/messages_' . substr($_SESSION['languages_code'], 0, strpos($_SESSION['languages_code'], '_')) . '.js'; ?>"></script>
<?php } ?>

<?php require "includes/template/javascript/zcJSFramework.js.php"; ?>

<script src="includes/general.js"></script>

<?php /** CDN for jQuery UI components **/ ?>
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.base.css" id="jQueryUIBaseCSS">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script>window.jQuery.Widget || document.write('<script src="includes/template/javascript/jquery-ui.min.js"><\/script>');</script>

<script src="includes/template/javascript/jquery-ui-i18n.min.js"></script>
<script>
// init datepicker defaults with localization
$(function(){
  $.datepicker.setDefaults($.extend({}, $.datepicker.regional["<?php echo $_SESSION['languages_code'] == 'en' ? '' : $_SESSION['languages_code']; ?>"], {
      showOn: "both",
      buttonImage: "images/calendar.gif",
      dateFormat: '<?php echo DATE_FORMAT_DATEPICKER_ADMIN; ?>',
      changeMonth: true,
      changeYear: true
  }) );
});
</script>
<link rel="stylesheet" type="text/css" href="includes/template/css/jquery.ui.theme.css" id="jQueryUIThemeCSS">

<link rel="stylesheet" type="text/css" href="includes/template/css/menu.css" id="menuCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/cssjsmenuhover.css" media="all" id="hoverJS">
<script src="includes/menu.js"></script>
<script>
  $(document).ready(function(){ cssjsmenuinit(); });
</script>

