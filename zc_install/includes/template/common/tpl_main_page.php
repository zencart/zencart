<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 19 Modified in v1.5.7 $
 */
?>
<body id="<?php echo $body_id; ?>">
  <header class="header">
    <div class="container">
      <div class="row">
        <div class="small-12 columns small-centered hero-unit">
            <div class="logo"></div>
            <div class="version"><?php echo sprintf(ZC_VERSION_STRING, PROJECT_VERSION_NAME, PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR); ?></div>
        </div>
      </div>
    </div>
  </header>
  <div class="container">
    <div class="row">
      <div class="small-12 columns small-centered">
        <div class="mainContent">
        <?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_breadcrumb.php'); ?>
        <?php if (!isset($_GET['main_page']) || $_GET['main_page'] == 'index' && count($languagesInstalled) > 1) { ?>
        <form name="language_select" id="language_select" method="GET">
          <fieldset>
           <div class="row">
             <div class="small-3 columns">
               <label class="inline" for="lng"><a href="#" class="hasHelpText" id="choose_lang"><?php echo TEXT_INSTALLER_CHOOSE_LANGUAGE; ?></a></label>
             </div>
             <div class="small-9 columns">
               <select name="lng" id="lng" class="medium"><?php echo zen_get_install_languages_list($installer_lng); ?></select>
             </div>
           </div>
           </fieldset>
        </form>
        <?php } ?>
        <h1><?php echo constant('TEXT_PAGE_HEADING_' . strtoupper($_GET['main_page'])); ?></h1>
        <?php if (defined('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN'))) {
                if (constant('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN')) != '') { ?>
        <div class="alert-box"><?php echo constant('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN')); ?></div>
        <?php  }
             } elseif (TEXT_HEADER_MAIN != '') { ?>
          <div class="alert-box"><?php echo TEXT_HEADER_MAIN; ?></div>
        <?php } ?>
        <?php require($body_code); ?>
        </div>
         <footer class="footer">
           <p>Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="https://www.zen-cart.com" rel="noopener" target="_blank">Zen Cart&reg;</a></p>
         </footer>
      </div>
      </div>
  </div>

<!-- Initialize Javascript components -->
<script src="<?php echo DIR_WS_INSTALL_TEMPLATE . 'foundation/foundation.min.js'; ?>"></script>
<?php if (file_exists(DIR_WS_INSTALL_TEMPLATE . 'foundation/foundation.zencart.extensions.js')) { ?>
<script src="<?php echo DIR_WS_INSTALL_TEMPLATE . 'foundation/foundation.zencart.extensions.js'; ?>"></script>
<?php } ?>
<script>
$(document)
  .foundation({
   abide: {
    patterns: {
      email: /^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@(((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+(XN\-\-[a-z0-9]{2,20}|[a-z]{2,20}))$/i,
      url: /^(https?|s?ftp):\/\/((([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\dA-Fa-f][\dA-Fa-f])|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\dA-Fa-f]{1,}\.(([A-Za-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:){1,}))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([A-Za-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([A-Za-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([A-Za-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([A-Za-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([A-Za-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([A-Za-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?(\/((([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\dA-Fa-f][\dA-Fa-f])|[!\$&'\(\)\*\+,;=]|:|@){1,}(\/(([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\dA-Fa-f][\dA-Fa-f])|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\dA-Fa-f][\dA-Fa-f])|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([A-Za-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\dA-Fa-f][\dA-Fa-f])|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
      password: /.*$/
    }
   }
  });
$().ready(function()
{
  $(".reveal-modal").find('.dismiss').click(function()
  {
    $('a.close-reveal-modal').trigger('click');
   });
   $('#lng').change(function(e) {
    $('#language_select').submit();
   });
});
</script>

</body>
</html>
