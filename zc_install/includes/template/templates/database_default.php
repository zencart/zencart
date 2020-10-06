<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 19 Modified in v1.5.7 $
 */
?>
<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_progress_bar.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_connection_errors.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_install_errors.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php'); ?>

<form id="db_setup" name="db_setup" method="post" action="index.php?main_page=admin_setup" data-abide="ajax">
  <input type="hidden" name="action" value="process" >
  <input type="hidden" name="lng" value="<?php echo $installer_lng; ?>" >
  <?php foreach ($_POST as $key=>$value) {  ?>
  <?php if ($key != 'action') { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" >
  <?php }?>
  <?php }?>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_SETTINGS; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="db_host"><a href="#" class="hasHelpText" id="DBHOST"><?php echo TEXT_DATABASE_SETUP_DB_HOST; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="db_host" id="db_host" value="<?php echo $db_host; ?>" tabindex="1" autofocus="autofocus" placeholder="<?php echo htmlentities(TEXT_EXAMPLE_DB_HOST, ENT_QUOTES); ?>" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_DBHOST; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="db_user"><a href="#" class="hasHelpText" id="DBUSER"><?php echo TEXT_DATABASE_SETUP_DB_USER; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="db_user" id="db_user" value="<?php echo $db_user; ?>" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_DB_USER; ?>" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_DBUSER; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="db_password"><a href="#" class="hasHelpText" id="DBPASSWORD"><?php echo TEXT_DATABASE_SETUP_DB_PASSWORD; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="password" name="db_password" id="db_password" value="<?php echo $db_password; ?>" tabindex="3" placeholder="<?php echo TEXT_EXAMPLE_DB_PWD; ?>">
        <small class="error"><?php echo TEXT_HELP_CONTENT_DBPASSWORD; ?></small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="db_name"><a href="#" class="hasHelpText" id="DBNAME"><?php echo TEXT_DATABASE_SETUP_DB_NAME; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="db_name" id="db_name" value="<?php echo $db_name; ?>" tabindex="4" placeholder="<?php echo TEXT_EXAMPLE_DB_NAME; ?>" required>
        <small class="error"><?php echo TEXT_HELP_CONTENT_DBNAME; ?></small>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_DEMO_SETTINGS; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="demoData"><a href="#" class="hasHelpText" id="DEMODATA"><?php echo TEXT_DATABASE_SETUP_LOAD_DEMO; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="checkbox" name="demoData" id="demoData" tabindex="5" <?php echo $install_demo_data ? 'checked' : ''; ?>><label class="inline" for="demoData"><?php echo TEXT_DATABASE_SETUP_LOAD_DEMO_DESCRIPTION; ?></label>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_ADVANCED_SETTINGS; ?></legend>
    <input type="hidden" name="db_charset" value="utf8mb4">
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="db_prefix"><a href="#" class="hasHelpText" id="DBPREFIX"><?php echo TEXT_DATABASE_SETUP_DB_PREFIX; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="db_prefix" id="db_prefix"  value="<?php echo $db_prefix; ?>" tabindex="7" placeholder="<?php echo TEXT_EXAMPLE_DB_PREFIX; ?>">
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="sql_cache_method"><a href="#" class="hasHelpText" id="SQLCACHEMETHOD"><?php echo TEXT_DATABASE_SETUP_SQL_CACHE_METHOD; ?></a></label>
      </div>
      <div class="small-9 columns">
        <select name="sql_cache_method" id="sql_cache_method" tabindex="8" ><?php echo $sqlCacheTypeOptions; ?></select>
      </div>
    </div>
  </fieldset>
  <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>" tabindex="10" >
</form>
<script>
$().ready(function() {
  $("#db_setup").on('valid.fndtn.abide', function(form){
      ajaxTestDBConnection(this);
  });
});

function ajaxTestDBConnection(form) {
  var str = $(form).serialize();
  var myform = form;
  $.ajax({
    type: "POST",
    dataType: "json",
    data: str,
    url: '<?php echo "ajaxTestDBConnection.php"; ?>',
    success: function(data) {
      if (data.error == true)
      {
        var dbErrorList = data.errorList['extraErrors'];
        var errorString = '';
        for (i in dbErrorList)
        {
          errorString += '<p>'+dbErrorList[i]+'</p>';
        }
        var html = data.errorList['mainErrorText'] + errorString;
        $("#connection-errors-content").html(html);
        $("#connection-errors").foundation('reveal', 'open');
      } else
      {
        $("#progress-bar-dialog").foundation('reveal', 'open', {close_on_background_click:false});
        t = setTimeout("updateStatus()", 300);
        $.ajax({
          type: "POST",
          timeout: 0,
          dataType: "json",
          data: str,
          url: '<?php echo "ajaxLoadMainSql.php"; ?>',
          success: function(data) {
            if (data.error == true)
            {
              var html = "<p><?php echo TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS1; ?>" + data.file + "<?php echo TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS2; ?></p>";
              $("#install-errors-content").html(html);
              $("#install-errors").foundation('reveal', 'open');
            } else
            {
              $.ajax({
                type: "POST",
                timeout: 0,
                dataType: "json",
                data: str,
                url: '<?php echo "ajaxAdminSetup.php"; ?>',
                success: function(data) {
                  $('#db_setup').append(
                      $('<input/>')
                          .attr('type', 'hidden')
                          .attr('name', 'adminDir')
                          .val(data.adminDir)
                  );
                  $('#db_setup').append(
                      $('<input/>')
                          .attr('type', 'hidden')
                          .attr('name', 'changedDir')
                          .val(data.changedDir)
                  );
                  $('#db_setup').append(
                      $('<input/>')
                          .attr('type', 'hidden')
                          .attr('name', 'adminNewDir')
                          .val(data.adminNewDir)
                  );
                  myform.submit();
                }
              });
            }
          }
        });
      }
    }
  });
}

function updateStatus() {
  $.ajax({
    type: "GET",
    dataType: "json",
    cache : false,
    url: '<?php echo "ajaxGetProgressValues.php"; ?>',
    success: function(data) {
      if (data.progress)
      {
        if (data.message)
        {
          $('#dialog-title').html(data.message + ' ' + data.progress.toFixed( 0 ) + '%');
        }
        if (data.progress >= 0 && data.progress < 99) {
          $("#progress-bar").html('<span class="meter" style="width:'+data.progress+'%;"></span>');
          t = setTimeout("updateStatus()", 200);
        }
      } else
      {
        t = setTimeout("updateStatus()", 10);
      }
    },
    error: function(data) {
      t = setTimeout("updateStatus()", 10);
    }
  });
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
