<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_progress_bar.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_connection_errors.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_install_errors.php'); ?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php'); ?>

<form class="form-horizontal" id="db_setup" name="db_setup" method="post" action="index.php?main_page=admin_setup">
  <input type="hidden" name="action" value="process" >
  <input type="hidden" name="lng" value="<?php echo $lng; ?>" >
  <?php foreach ($_POST as $key=>$value) {  ?>
  <?php if ($key != 'action') { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" >
  <?php }?>
  <?php }?>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_SETTINGS; ?></legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_host"><a href="#" class="hasHelpText" id="DBHOST"><?php echo TEXT_DATABASE_SETUP_DB_HOST; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="text" name="db_host" id="db_host" value="<?php echo $db_host; ?>" tabindex="1" >
      </div>
    </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_user"><a href="#" class="hasHelpText" id="DBUSER"><?php echo TEXT_DATABASE_SETUP_DB_USER; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="text" name="db_user" id="db_user" value="<?php echo $db_user; ?>" tabindex="2" >
      </div>
    </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_password"><a href="#" class="hasHelpText" id="DBPASSWORD"><?php echo TEXT_DATABASE_SETUP_DB_PASSWORD; ?></a></label>      
      </div>
      <div class="six columns end">
        <input type="password" name="db_password" id="db_password" value="<?php echo $db_password; ?>" tabindex="3" >
      </div>
    </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_name"><a href="#" class="hasHelpText" id="DBNAME"><?php echo TEXT_DATABASE_SETUP_DB_NAME; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="text" name="db_name" id="db_name" value="<?php echo $db_name; ?>" tabindex="4" >
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_DEMO_SETTINGS; ?></legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="demoData"><a href="#" class="hasHelpText" id="DEMODATA"><?php echo TEXT_DATABASE_SETUP_LOAD_DEMO; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="checkbox" name="demoData" id="demoData" tabindex="5" >
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?php echo TEXT_DATABASE_SETUP_ADVANCED_SETTINGS; ?></legend>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_charset"><a href="#" class="hasHelpText" id="DBCHARSET"><?php echo TEXT_DATABASE_SETUP_DB_CHARSET; ?></a></label>
      </div>
      <div class="six columns end">
        <select name="db_charset" id="db_charset" tabindex="6" ><?php echo $dbCharsetOptions; ?></select>
      </div>
    </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="db_prefix"><a href="#" class="hasHelpText" id="DBPREFIX"><?php echo TEXT_DATABASE_SETUP_DB_PREFIX; ?></a></label>
      </div>
      <div class="six columns end">
        <input type="text" name="db_prefix" id="db_prefix"  value="<?php echo $db_prefix; ?>" tabindex="7" >
      </div>
    </div>
    <div class="row">
      <div class="three columns">
        <label class="inline" for="sql_cache_method"><a href="#" class="hasHelpText" id="SQLCACHEMETHOD"><?php echo TEXT_DATABASE_SETUP_SQL_CACHE_METHOD; ?></a></label>
      </div>
      <div class="six columns end">
        <select name="sql_cache_method" id="sql_cache_method" tabindex="8" ><?php echo $sqlCacheTypeOptions; ?></select>
      </div>
    </div>
    <div class="row">
      <div id="sql-cache-directory-input">
        <div class="three columns">
          <label class="inline" for="sql_cache_dir"><a href="#" class="hasHelpText" id="SQLCACHEDIRECTORY"><?php echo TEXT_DATABASE_SETUP_SQL_CACHE_DIRECTORY; ?></a></label>
        </div>
        <div class="six columns end">
          <input type="text" name="sql_cache_dir" id="sql_cache_dir" value="<?php echo $sql_cache_dir; ?>" tabindex="9" >
        </div>
      </div>
    </div>
  </fieldset>      
  <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>" tabindex="10" >
</form> 
<script>  
$(function() {
	if ($('#sql_cache_method').val() == 'file') 
	{
     $('#sql-cache-directory-input').show();
	} else 
	{
	  $('#sql-cache-directory-input').hide();
  }
  $('#sql_cache_method').change(function () {
		  if ($('#sql_cache_method').val() == 'file') 
		  {
	       $('#sql-cache-directory-input').show();
		  } else 
		  {
			  $('#sql-cache-directory-input').hide();
		  }
  });
});
        
$().ready(function() {
  $("#db_setup").validate({
    submitHandler: function(form) {
      ajaxTestDBConnection(form);
    },
    rules: {
      db_host: "required",
      db_user: "required",
      db_name: "required",
      sql_cache_dir: {
        required: function(element) {
          return $("#sql_cache_method").val() == 'file';
        }
      }  
    },
    messages: {
    }
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
        $("#connection-errors").reveal();
      } else
      {
        $("#progress-bar-dialog").reveal();
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
              var html = "<?php echo TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS1; ?>" + data.file + "<?php echo TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS2; ?>";
              $("#install-errors-content").html(html);
              $("#install-errors").reveal();            
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
          $('#dialog-title').html(data.message);
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