<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 19 Modified in v1.5.7 $
 */
require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php');
?>
<form id="system_setup" name="system_setup" method="post" action="index.php?main_page=database" data-abide="ajax">
  <input type="hidden" name="action" value="process">
  <input type="hidden" name="lng" value="<?php echo $installer_lng; ?>" >
  <input type="hidden" name="dir_ws_http_catalog" value="<?php echo $dir_ws_http_catalog; ?>">
  <input type="hidden" name="dir_ws_https_catalog" value="<?php echo $dir_ws_https_catalog; ?>">
  <input type="hidden" name="detected_detected_http_server_catalog" value="<?php echo $catalogHttpServer; ?>">
  <input type="hidden" name="detected_detected_https_server_catalog" value="<?php echo $catalogHttpsServer; ?>">
  <input type="hidden" name="adminDir" value="<?php echo $adminDir; ?>">
  <input type="hidden" name="db_type" value="<?php echo $db_type; ?>">
  <fieldset>
    <legend>License</legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="agreeLicense"><a href="#" class="hasHelpText" id="AGREETOTERMS"><?php echo TEXT_SYSTEM_SETUP_AGREE_LICENSE; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="checkbox" name="agreeLicense" id="agreeLicense" tabindex="1" required > <label class="inline" for="agreeLicense"><?php echo TEXT_SYSTEM_SETUP_CLICK_TO_AGREE_LICENSE; ?></label>
        <small class="error"><?php echo TEXT_FORM_VALIDATION_AGREE_LICENSE; ?></small>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_SYSTEM_SETUP_ADMIN_SETTINGS; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="http_server_admin"><a href="#" class="hasHelpText" id="ADMINSERVERDOMAIN"><?php echo TEXT_SYSTEM_SETUP_ADMIN_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="http_server_admin" type="text" value="<?php echo $adminServer; ?>" name="http_server_admin" tabindex="2" placeholder="ie: https:/www.your_domain.com" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_ADMINSERVERDOMAIN; ?></small>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_SYSTEM_SETUP_CATALOG_SETTINGS; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="enable_ssl_catalog"><a href="#" class="hasHelpText" id="ENABLESSLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input class="checkbox" id="enable_ssl_catalog" type="checkbox" value="true" name="enable_ssl_catalog" tabindex="3" <?php echo $enableSslCatalog; ?>><label class="inline" for="enable_ssl_catalog"><?php echo TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL; ?></label>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="http_server_catalog"><a href="#" class="hasHelpText" id="HTTPSERVERCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTP_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="http_server_catalog" type="url" value="<?php echo $catalogHttpServer; ?>" name="http_server_catalog" tabindex="4" placeholder="ie: http:/www.your_domain.com" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_HTTPSERVERCATALOG; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="http_url_catalog"><a href="#" class="hasHelpText" id="HTTPURLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTP_URL; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="http_url_catalog" type="url" value="<?php echo $catalogHttpUrl; ?>" name="http_url_catalog" tabindex="5" placeholder="ie: http:/www.your_domain.com">
        <small class="error"><?php echo TEXT_HELP_CONTENT_HTTPURLCATALOG; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="https_server_catalog"><a href="#" class="hasHelpText" id="HTTPSSERVERCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTPS_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="https_server_catalog" type="url" value="<?php echo $catalogHttpsServer; ?>" name="https_server_catalog" tabindex="6" placeholder="ie: https:/www.your_domain.com" required>
        <small class="error"><?php echo TEXT_FORM_VALIDATION_CATALOG_HTTPS_URL; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="https_url_catalog"><a href="#" class="hasHelpText" id="HTTPSURLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTPS_URL; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="https_url_catalog" type="url" value="<?php echo $catalogHttpsUrl; ?>" name="https_url_catalog" tabindex="7" placeholder="ie: https:/www.your_domain.com">
        <small class="error"><?php echo TEXT_HELP_CONTENT_HTTPSURLCATALOG; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="physical_path"><a href="#" class="hasHelpText" id="PHYSICALPATH"><?php echo TEXT_SYSTEM_SETUP_CATALOG_PHYSICAL_PATH; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input id="physical_path" type="text" value="<?php echo $documentRoot; ?>" name="physical_path" tabindex="8"  placeholder="ie: /yourserver/users/yourname/public_html/zencart" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_PHYSICALPATH; ?></small>
      </div>
    </div>
  </fieldset>
  <input type="submit" id="btnsubmit" class="radius button" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>" tabindex="9" >
</form>

<?php
require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_system_setup_errors.php');
?>

<script>
$().ready(function() {
  $("#system_setup").on('valid.fndtn.abide', function() {

   var str = $(this).serialize();
   var myform = this;
   $.ajax({
     type: "POST",
     dataType: "json",
     data: str,
     url: '<?php echo "ajaxTestSystemSetup.php"; ?>',
     success: function(data) {
       if (!data.error)
       {
         myform.submit();
       } else
       {
         var errorList = data.errorList;
         var errorString = '';
         for (i in errorList)
         {
           errorString += '<p>'+errorList[i]+'</p>';
         }
         $("#system-setup-errors-content").html(errorString) ;
         $("#system-setup-errors").foundation('reveal', 'open');
         $("#system-setup-errors-submit").click(function()
         {
           myform.submit();
         });
       }
     }
   });
  })
})
$(function()
    {
      $('.hasNoHelpText').click(function(e)
      {
        e.preventDefault();
      })
      $('.hasHelpText').click(function(e)
      {
        var textId = $(this).attr('id');
        $.ajax({
          type: "POST",
          timeout: 100000,
          dataType: "json",
          data: 'id='+textId + '&lng=<?php echo $installer_lng; ?>',
          url: '<?php echo "ajaxGetHelpText.php"; ?>',
           success: function(data) {
             $('#modal-help-title').html(data.title);
             $('#modal-help-content').html(data.text);
             $('#modal-help').foundation('reveal', 'open');
          }
        });
        e.preventDefault();
      })
    });
</script>
