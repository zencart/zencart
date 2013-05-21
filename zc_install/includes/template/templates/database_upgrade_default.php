<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
?>
<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_admin_validation_errors.php'); ?>

<div id="upgradeResponsesHolder"></div>

<form class="form-horizontal" id="db_upgrade" name="db_upgrade" method="post" action="index.php?main_page=completion">
  <input type="hidden" name="lng" value="<?php echo $lng; ?>" >
  <input type="hidden" name="action" value="process"> 
	<input type="hidden" name="upgrade_mode" value="yes"> 
	<input type="hidden" name="admin_candidate" value="" id="hiddenAdminCandidate">
  <fieldset>
	  <legend><?php echo TEXT_DATABASE_UPGRADE_LEGEND_UPGRADE_STEPS; ?></legend>
	  <div class="row">
    <div class="three columns end">
	  <?php foreach ($newArray as $key => $value)  { ?>
  		<?php $from = ($key == 0) ? $dbVersion : $newArray[($key - 1)]; ?>
  		<?php $to = $newArray[$key]; ?>
  	   <div id="label-version-<?php echo str_replace('.', '_', $newArray[$key]); ?>" class="checkbox-wrapper">
	        <label for="version-<?php echo str_replace('.', '_', $newArray[$key]); ?>">
		  	  <input type="checkbox" name="version-<?php echo str_replace('.', '_', $newArray[$key]); ?>" id="version-<?php echo str_replace('.', '_', $newArray[$key]); ?>" checked="CHECKED">
		  	  <?php echo $from . ' to  ' . $to;  ?></label>
		  </div>
		<?php } ?>
		</div>
		</div>
	</fieldset>
	<fieldset class="upgrade-hide-area">
	  <legend><?php echo TEXT_DATABASE_UPGRADE_ADMIN_CREDENTIALS; ?></legend>
	  <div class="row">
      <div class="three columns">
		    <label class="inline left" for="admin_user"><?php echo TEXT_DATABASE_UPGRADE_ADMIN_USER; ?></label>
	  	</div>
		  <div class="six columns end"> 
		    <input type="text" name="admin_user" id="admin_user" value="">
		  </div>
    </div>
	  <div class="row">
      <div class="three columns">
		    <label class="inline left" for="admin_password"><?php echo TEXT_DATABASE_UPGRADE_ADMIN_PASSWORD; ?></label>
  		</div>  
		  <div class="six columns end"> 
		    <input type="password" name="admin_password" id="admin_password" value="">
	  	</div>
	  </div>
	</fieldset>
	<div class="upgrade-hide-area">
		<input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>">
	</div>
</form>

<script>

$().ready(function() {
  $("#db_upgrade").validate({
	  errorElement: 'span',
	  errorClass: 'help-inline invalid',  
    submitHandler: function(form) {
      $('#upgradeResponsesHolder').html('');
      var str = $(form).serialize();
      $.ajax({
        type: "POST",
        dataType: "json",
        data: str,
        url: '<?php echo "ajaxValidateAdminCredentials.php"; ?>',
        success: function(data) {
          if (data.error)
          {
            $('#admin-validation-errors-content').html('<p>Could not verify the Admin Credentials you provided.<p>');
            $('#admin-validation-errors').reveal();
          } else 
          {
            $('#hiddenAdminCandidate').val(data.adminCandidate);
            $('#admin_password').val('');
            $('.upgrade-hide-area').hide();
            doAjaxUpdateSql(form);
          }
        }
      })
    },
    rules: {
      admin_user: "required",
      admin_password: "required",
    }
  }); 
});

function doAjaxUpdateSql(form)
{
  var deferred = $.Deferred();
  var promise = deferred.promise();
  var length = $('input[type=checkbox]:checked').length;
  var error = false;
  $('input[type=checkbox]:checked').each(function() {
    var version = $(this).attr('id');
    promise=promise.pipe( 
      function(response, status, ajax) {
        if (response && response.error)
        {
          deferred.reject();
          error = true;
          return promise;          
        } else 
        {
          return doRequest(version);
        }
      },
      function(response, status, ajax) {
      }
    ) 
    .done(function (response, status, ajax) {
      if (response.error)  
      {
        error = true;
        var errorList = response.errorList;
        var errorString = '';
        for (i in errorList)
        {
          errorString += '<p>'+errorList[i]+'</p>';
        }
        $('#upgradeResponsesHolder').append('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button>'+errorString+'</div>')          
        $('.upgrade-hide-area').show();
      } else 
      {
        id = response.version.replace('version-', ''); 
        id = id.replace(/_/g, '.'); 
        $('#label-' + version).hide(); 
        $('#upgradeResponsesHolder').append('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>Upgrade to Version ' + id + ' completed.</div>');
      }
    });
  });
  deferred.resolve();
  promise.done(function(response) {
    if (!error)
    {
      form.submit();
    } 
  });
 
  function doRequest(version) {
    return $.ajax({
      type: "post",
      url: "ajaxLoadUpdatesSql.php",
      data: {version:version},
      dataType: "JSON"
    });
  }  	
}
</script>