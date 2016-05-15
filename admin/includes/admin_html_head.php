<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_html_head.php  New in v1.6.0 $
 */
?>
<!doctype html>
<!--[if IE 9]><html class="lt-ie10" <?php echo HTML_PARAMS; ?> > <![endif]-->
<html class="no-js" <?php echo HTML_PARAMS; ?> >

<head>
<meta charset="<?php echo CHARSET; ?>">
<title><?php echo ADMIN_TITLE; ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>

<link rel="stylesheet" type="text/css" href="includes/template/css/normalize.css" id="normalizeCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet.css" id="stylesheetCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet_print.css" media="print" id="printCSS">

<?php if (isset($extraCss)) { ?>
  <?php foreach ($extraCss as $css) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $css['location']; ?>">
  <?php } ?>
<?php } ?>

<!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script> -->
<!-- <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->

<script src="includes/template/javascript/jquery-1.12.1.min.js"></script>
<script src="includes/template/javascript/bootstrap.min.js"></script>


<script src="includes/general.js"></script>

<link rel="stylesheet" type="text/css" href="includes/template/css/jquery-ui.min.css" id="jQueryUIThemeCSS">
<?php /** CDN for jQuery UI components **/ ?>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
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

<link rel="stylesheet" type="text/css" href="includes/template/css/menu.css" id="menuCSS">
