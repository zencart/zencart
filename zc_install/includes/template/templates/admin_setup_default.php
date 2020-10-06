<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 19 Modified in v1.5.7 $
 */
?>

<?php require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php'); ?>

<?php if (count($errors)) { ?>
    <div class="alert-box alert">
<?php
      foreach ($errors as $errormessage) {
        echo $errormessage . '<br>';
      }
      echo TEXT_ERROR_PROBLEMS_WRITING_CONFIGUREPHP_FILES;
?>
    </div>
<?php } ?>

<form id="admin_setup" name="admin_setup" method="post" action="index.php?main_page=completion" data-abide>
  <input type="hidden" name="action" value="process" >
  <input type="hidden" name="lng" value="<?php echo $installer_lng; ?>" >
  <?php foreach ($_POST as $key=>$value) {  ?>
    <?php if ($key != 'action') { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" >
    <?php }?>
  <?php }?>
  <fieldset>
    <legend><?php echo TEXT_ADMIN_SETUP_USER_SETTINGS; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="admin_user"><a href="#" class="hasHelpText" id="ADMINUSER"><?php echo TEXT_ADMIN_SETUP_USER_NAME; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_user" id="admin_user" value="" tabindex="1" autofocus="autofocus" placeholder="<?php echo TEXT_EXAMPLE_USERNAME; ?>" required autocomplete="off">
        <small class="error">A unique admin username is required</small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="admin_email"><a href="#" class="hasHelpText" id="ADMINEMAIL"><?php echo TEXT_ADMIN_SETUP_USER_EMAIL; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_email" id="admin_email" value="" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_EMAIL; ?>" required pattern="email" autocomplete="off">
        <small class="error">A valid email address is required</small>
      </div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="admin_email2"><a href="#" class="hasHelpText" id="ADMINEMAIL2"><?php echo TEXT_ADMIN_SETUP_USER_EMAIL_REPEAT; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_email2" id="admin_email2" value="" tabindex="3" placeholder="<?php echo TEXT_EXAMPLE_EMAIL; ?>" required pattern="email" data-equalto="admin_email" autocomplete="off">
        <small class="error">A matching valid email address is required.</small>
      </div>
    </div>
    <div class="row">
      <div class="alert-box alert"><?php echo TEXT_ADMIN_SETUP_USER_PASSWORD_HELP; ?></div>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="admin_password"><a href="#" class="hasHelpText" id="ADMINPASSWORD"><?php echo TEXT_ADMIN_SETUP_USER_PASSWORD; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_password" id="admin_password" value="<?php echo $admin_password; ?>" readonly="readonly" tabindex="4" autocomplete="off">
      </div>
    </div>
    <div class="row">
      <?php if ($changedDir) { ?>
        <div class="alert-box"><?php echo TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_CHANGED; ?></div>
      <?php } elseif (!$changedDir && $adminNewDir == 'admin') { ?>
        <div class="alert-box alert"><?php echo TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_DEFAULT; ?></div>
      <?php } else { ?>
        <div class="alert-box "><?php echo TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_NOT_ADMIN_CHANGED; ?></div>
      <?php }?>
    </div>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="admin_directory"><a href="#" class="hasHelpText" id="ADMINDIRECTORY"><?php echo TEXT_ADMIN_SETUP_ADMIN_DIRECTORY; ?></a></label>
      </div>
      <div class="small-9 columns">
        <input type="text" name="admin_directory" id="admin_directory" value="<?php echo $adminNewDir; ?>" readonly="readonly" tabindex="5" autocomplete="off">
      </div>
    </div>
  </fieldset>
  <input class="radius button" type="submit" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_CONTINUE; ?>" tabindex="10">
</form>

<script>
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
