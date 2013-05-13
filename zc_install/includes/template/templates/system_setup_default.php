<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php');
?>
<form class="form-horizontal" id="system_setup" name="system_setup" method="post" action="index.php?main_page=database">
	<input type="hidden" name="action" value="process">
	<input type="hidden" name="dir_ws_http_catalog" value="<?php echo $dir_ws_http_catalog; ?>">
	<input type="hidden" name="dir_ws_https_catalog" value="<?php echo $dir_ws_https_catalog; ?>">
	<input type="hidden" name="adminDir" value="<?php echo $adminDir; ?>">
	<input type="hidden" name="db_type" value="<?php echo $db_type; ?>">
  <fieldset>
    <legend>License</legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="agreeLicense"><a href="#" class="hasHelpText" id="AGREETOTERMS"><?php echo TEXT_SYSTEM_SETUP_AGREE_LICENSE; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="checkbox" name="agreeLicense" id="agreeLicense" >
      </div>
      </div>
  </fieldset>                                                                                                                                                                 
  <fieldset>
	  <legend><?php echo TEXT_SYSTEM_SETUP_ADMIN_SETTINGS; ?></legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="http_server_admin"><a href="#" class="hasHelpText" id="ADMINSERVERDOMAIN"><?php echo TEXT_SYSTEM_SETUP_ADMIN_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="http_server_admin" type="text" value="<?php echo $adminServer; ?>" name="http_server_admin">
      </div>
	  </div>
	</fieldset>
  <fieldset>
	  <legend><?php echo TEXT_SYSTEM_SETUP_CATALOG_SETTINGS; ?></legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="enable_ssl_catalog"><a href="#" class="hasHelpText" id="ENABLESSLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL; ?></a></label>
      </div>
      <div class="nine columns">
        <input class="checkbox" id="enable_ssl_catalog" type="checkbox" value="true" name="enable_ssl_catalog">
      </div>
	  </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="http_server_catalog"><a href="#" class="hasHelpText" id="HTTPSERVERCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTP_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="http_server_catalog" type="text" value="<?php echo $catalogHttpServer; ?>" name="http_server_catalog">
      </div>
		</div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="http_url_catalog"><a href="#" class="hasHelpText" id="HTTPURLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTP_URL; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="http_url_catalog" type="text" value="<?php echo $catalogHttpUrl; ?>" name="http_url_catalog">
      </div>
		</div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="https_server_catalog"><a href="#" class="hasHelpText" id="HTTPSSERVERCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTPS_SERVER_DOMAIN; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="https_server_catalog" type="text" value="<?php echo $catalogHttpsServer; ?>" name="https_server_catalog">
      </div>
		</div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="https_url_catalog"><a href="#" class="hasHelpText" id="HTTPSURLCATALOG"><?php echo TEXT_SYSTEM_SETUP_CATALOG_HTTPS_URL; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="https_url_catalog" type="text" value="<?php echo $catalogHttpsUrl; ?>" name="https_url_catalog">
      </div>
		</div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="physical_path"><a href="#" class="hasHelpText" id="PHYSICALPATH"><?php echo TEXT_SYSTEM_SETUP_CATALOG_PHYSICAL_PATH; ?></a></label>
      </div>
      <div class="nine columns">
        <input id="physical_path" type="text" value="<?php echo $documentRoot; ?>" name="physical_path">
      </div>
		</div>
	</fieldset>
  <input type="submit" id="btnsubmit" class="radius button" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>">
</form>

<?php 
require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_system_setup_errors.php');
?>

<script>  
$().ready(function() {
  $("#system_setup").validate({
    submitHandler: function(form) {
      var str = $(form).serialize();
      var myform = form;	
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
            $("#system-setup-errors").reveal();
            $("#system-setup-errors-submit").click(function()
            {
              myform.submit();
            });
          } 
        }
      });
    },
    rules: {
      agreeLicense: "required",
      http_server_admin: "required",
      http_server_catalog: "required",
      https_server_catalog: "required",
      physical_path: "required",
      admin_physical_path: "required"
    },
    messages: {
     agreeLicense: "<?php echo TEXT_FORM_VALIDATION_AGREE_LICENSE; ?>"
    }
  }); 
});
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
			    data: 'id='+textId,
		      url: '<?php echo "ajaxGetHelpText.php"; ?>',
			   	success: function(data) {
		     	  $('#modal-help-title').html(data.title);
			     	$('#modal-help-content').html(data.text);
			       $('#modal-help').reveal();
		      }
		    });
			  e.preventDefault(); 
		  })  
		});
</script>
