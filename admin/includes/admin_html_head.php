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

<link rel="stylesheet" type="text/css" href="includes/template/css/bootstrap.min.css" id="bootstrapCSS">
<!-- <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
<!-- <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"> -->

<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" id="fontAwesomeCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet.css" id="stylesheetCSS">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet_print.css" media="print" id="printCSS">

<?php if (isset($extraCss)) { ?>
  <?php foreach ($extraCss as $css) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $css['location']; ?>">
  <?php } ?>
<?php } ?>


<?php /** CDN for jQuery core **/ ?>
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="includes/template/javascript/jquery-2.2.4.min.js"><\/script>');</script>
<!-- <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script> -->
<script src="includes/template/javascript/bootstrap.min.js"></script>



<script src="includes/general.js"></script>

<link rel="stylesheet" type="text/css" href="includes/template/css/jquery-ui.min.css" id="jQueryUIThemeCSS">
<?php /** CDN for jQuery UI components **/ ?>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
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
