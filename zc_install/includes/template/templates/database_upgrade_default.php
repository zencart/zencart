<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 19 Modified in v1.5.7 $
 */
?>
<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_admin_validation_errors.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php'); ?>

<?php if (sizeof($newArray)) { ?>
<div class="upgrade-progress-area">
  <div class="alert-box" id="upgradeHeaderMessage"><?php echo TEXT_DATABASE_UPGRADE_STEPS_DETECTED; ?></div>
</div>
<?php } ?>
<div id="upgradeResponsesHolder"></div>

<form id="db_upgrade<?php echo (count($newArray)) ? '' : '_done'; ?>" name="db_upgrade" method="post" action="index.php?main_page=completion" data-abide="ajax">
  <input type="hidden" name="lng" value="<?php echo $installer_lng; ?>" >
  <input type="hidden" name="action" value="process">
<?php if (sizeof($newArray)) { ?>
  <input type="hidden" name="upgrade_mode" value="yes">
  <fieldset id="availableUpgradeSteps">
    <legend><?php echo TEXT_DATABASE_UPGRADE_LEGEND_UPGRADE_STEPS; ?></legend>
    <div class="row">

    <div class="small-12 columns">
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
      <div class="small-3 columns">
        <label class="inline left" for="admin_user"><a href="#" class="hasHelpText" id="UPGRADEADMINNAME"><?php echo TEXT_DATABASE_UPGRADE_ADMIN_USER; ?></a></label>

      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_user" id="admin_user" value="" tabindex="1" autofocus="autofocus" required>
        <small class="error"><?php echo TEXT_VALIDATION_ADMIN_CREDENTIALS; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline left" for="admin_password"><a href="#" class="hasHelpText" id="UPGRADEADMINPWD"><?php echo TEXT_DATABASE_UPGRADE_ADMIN_PASSWORD; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="password" name="admin_password" id="admin_password" value="" tabindex="2" required>
        <small class="error"><?php echo TEXT_VALIDATION_ADMIN_PASSWORD; ?></small>
      </div>
    </div>
  </fieldset>
<?php } else {?>
<div>
  <div class="alert-box success round"><?php echo TEXT_NO_REMAINING_UPGRADE_STEPS; ?></div>
</div>
<?php } ?>
  <div class="upgrade-continue-button">
    <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>" tabindex="3">
  </div>
</form>

<script>
$().ready(function() {
  $("#db_upgrade").on('valid.fndtn.abide', function(){
    var errorElement = 'span';
    var errorClass = 'help-inline invalid';
    $('#upgradeResponsesHolder').html('');
    var myform = this;
    var str = $(this).serialize();
    $.ajax({
      type: "POST",
      dataType: "json",
      data: str,
      url: '<?php echo "ajaxValidateAdminCredentials.php"; ?>',
      success: function(data) {
        if (data.error)
        {
          $('#admin-validation-errors-content').html('<p><?php echo TEXT_ERROR_ADMIN_CREDENTIALS;?></p>');
          $('#admin-validation-errors').foundation('reveal', 'open');
        } else
        {
          $('#admin_password').val('');
          $('#upgradeHeaderMessage').val('<?php echo TEXT_UPGRADE_IN_PROGRESS;?>');
          $('#upgradeHeaderMessage').addClass('secondary');
          $('.upgrade-hide-area').hide();
          $('.upgrade-continue-button').hide();
          doAjaxUpdateSql(myform);
        }
      }
    })
  });
});

function doAjaxUpdateSql(form)
{
  var deferred = $.Deferred();
  var promise = deferred.promise();
  var length = $('input[type=checkbox]:checked').length;
  var error = false;
  var errorList = null;
  var response = null;
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
        console.log('response.error='+response.error);
      if (response.error && response.error === true)
      {
        error = true;
        var errorList = response.errorList;
        var errorString = '';
        $('#upgradeResponsesHolder').append('<div class="alert-box alert round">' + errorList.join('<br>') + '</div>')
        $('.upgrade-hide-area').show();
        $('.upgrade-continue-button').show();
      } else
      {
        id = response.version.replace('version-', '');
        id = id.replace(/_/g, '.');
        $('#label-' + version).remove();
        var str = '<?php echo TEXT_UPGRADE_TO_VER_X_COMPLETED;?>';
        $('#upgradeResponsesHolder').append('<div class="alert-box success round">' + str.replace('%s', id) + '</div>');
      }
    });
  });
  deferred.resolve();
  promise.done(function(response) {
    $('.upgrade-progress-area').hide();
    var length = $('input[type=checkbox]:not(:checked)').length;
    console.log('DB Upgrade progress. Remaining checkboxes: '+length);
    if (length == 0) {
      $('#availableUpgradeSteps').hide();
      $('.upgrade-continue-button').show();
      $("#db_upgrade").off('valid.fndtn.abide');
    }
    if (!error && length == 0)
    {
      form.submit();
    } else {
      $('.upgrade-hide-area').show();
      $('.upgrade-continue-button').show();
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