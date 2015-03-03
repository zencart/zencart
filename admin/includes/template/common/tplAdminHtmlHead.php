<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<!doctype html>
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php echo HTML_PARAMS; ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php echo HTML_PARAMS; ?>> <!--<![endif]-->
<head>
<meta charset="<?php echo CHARSET; ?>">
<title><?php echo ADMIN_TITLE; ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

<?php foreach ($tplVars['cssList'] as $entry ) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $entry['href']; ?>" <?php echo (isset($entry['id']) ? 'id="' . $entry['id'] . '"': ''); ?> <?php echo (isset($entry['media']) ? 'media="' . $entry['media'] . '"': ''); ?>>
<?php } ?>

<?php if (isset($extraCss)) { ?>
  <?php foreach ($extraCss as $css) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $css['location']; ?>">
  <?php } ?>
<?php } ?>

<script src="includes/template/javascript/foundation/modernizr.js"></script>

<?php /** CDN for jQuery core **/ ?>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script>window.jQuery || document.write('<script src="includes/template/javascript/foundation/jquery.min.js"><\/script>');</script>

<?php /** Load Foundation framework core**/ ?>
<script src="includes/template/javascript/foundation/foundation.min.js"></script>

<?php require "includes/template/javascript/zcJSFramework.js.php"; ?>

<script src="includes/general.js"></script>

<link rel="stylesheet" type="text/css" href="includes/template/css/jquery-ui.min.css" id="jQueryUIThemeCSS">
<?php /** CDN for jQuery UI components **/ ?>
<script src="//code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
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
<link rel="stylesheet" type="text/css" href="includes/template/css/cssjsmenuhover.css" media="all" id="hoverJS">
<script src="includes/menu.js"></script>
<!--<script src="includes/template/javascript/select2-master/select2.js"></script>-->
<script>
  $(document).ready(function(){ cssjsmenuinit(); });
</script>
