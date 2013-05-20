<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
?>
<body id="<?php echo $body_id; ?>">
  <header class="header">
    <div class="container">
      <div class="row">
        <div class="twelve columns centred hero-unit">
        <div class="logo"></div>
        </div>
      </div>
    </div>
  </header>
  <div class="container">
    <div class="row">
      <div class="twelve columns centred">
        <div class="mainContent">       
        <?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_breadcrumb.php'); ?>
        <h1><?php echo constant('TEXT_PAGE_HEADING_' . strtoupper($_GET['main_page'])); ?></h1>
        <?php if (defined('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN'))) { ?>
        <div class="alert-box"><?php echo constant('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN')); ?></div>
        <?php } ?>
        <?php require($body_code); ?>
        </div>
         <footer class="footer">
           <p>Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></p>
         </footer>
      </div>
      </div>
  </div>
<script>
$().ready(function() 
{
  $.validator.messages.required = '<?php echo TEXT_FORM_VALIDATION_REQUIRED; ?>';
  $(".reveal-modal").find('.dismiss').click(function()
  {
 	  $(".reveal-modal").trigger('reveal:close');
 	});
});  
</script>
</body>
</html>