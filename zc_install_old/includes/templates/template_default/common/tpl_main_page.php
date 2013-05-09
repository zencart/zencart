<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php 19537 2011-09-20 17:14:44Z drbyte $
 */

  $header_template = 'tpl_header.php';
  $footer_template = 'tpl_footer.php';
  $left_column_file = 'column_left.php';
  $body_id = str_replace('_', '', $_GET['main_page']);

?>
<body id="<?php echo $body_id; ?>" <?php echo $zc_first_field;?>>
<?php
  if ($messageStack->size('upgrade') > 0) {
    echo $messageStack->output('upgrade');
  }
?>
<div id="wrap">
  <div id="header">
  <img src="<?php echo DIR_WS_INSTALL_TEMPLATE; ?>images/zen_header_bg.jpg" alt="Zen Cart&reg; - The Art of eCommerce" title="Zen Cart&reg;"/>
  </div>
  <div id="content">
  <h1>:: <?php echo TEXT_PAGE_HEADING; ?></h1>
  <p><?php echo TEXT_MAIN; ?></p>
  <?php
  require($body_code);
  ?>
  </div>
  <div id="navigation">
  <?php
  require(DIR_WS_INSTALL_TEMPLATE . "sideboxes/navigation.php");
  ?>
  </div>
  <div id="footer">
    <p>Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></p>
  </div>
</div>
<!--  <p><a href="http://validator.w3.org/check?uri=referer">Valid XHTML 1.0 Transitional</a></p>-->
<?php
  if ($messageStack->size('upgrade-error-details') > 0) {
    echo $messageStack->output('upgrade-error-details');
  }
?>
<!-- <pre style="text-align: left"><?php print_r($zc_install->getConfigKey('-')); ?> </pre> -->

</body>
</html>