<?php
/**
 * Admin Start Wizard Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Updated in v1.6.0$
 */

$form_disabled = false;
?>
<style>
.invalid {
  color: #FF2222
}
</style>

<div class="container-fluid">
<div class="row">
  <div class="col-xs-8 col-md-5 col-md-offset-3 col-sm-8">

    <h1><?php echo TEXT_HEADING_SETUP_WIZARD; ?></h1>

    <?php
      if (!zen_is_superuser())
      {
        $form_disabled = true;
    ?>
      <div class="alert alert-error"><?php echo TEXT_WARNING_SUPERUSER_REQUIRED; ?></div>
    <?php } ?>


      <form id="setup-wizard" class="form-horizontal" method="post"<?php if ($form_disabled) echo 'disabled';?>>
      <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
      <input type="hidden" name="action" value="setupWizard">

      <fieldset>
      <legend><?php echo TEXT_FORM_LEGEND_REQUIRED_SETUP; ?></legend>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_name"><?php echo TEXT_FORM_LABEL_STORE_NAME; ?></label>
        <div class="col-sm-8 input-group">
          <input type="text" class="form-control" id="store_name" name="store_name" value="<?php echo zen_output_string_protected($tplVars['storeName']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STORENAME; ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_owner"><?php echo TEXT_FORM_LABEL_STORE_OWNER; ?></label>
        <div class="col-sm-8 input-group">
          <input type="text" class="form-control" id="store_owner" name="store_owner" value="<?php echo zen_output_string_protected($tplVars['storeOwner']); ?>" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNER; ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_owner_email"><?php echo TEXT_FORM_LABEL_STORE_OWNER_EMAIL; ?></label>
        <div class="col-sm-8 input-group">
          <input type="text" class="form-control" id="store_owner_email" name="store_owner_email" value="<?php echo zen_output_string_protected($tplVars['storeOwnerEmail']); ?>" tabindex="3" placeholder="webmaster@example.com">
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_country"><?php echo TEXT_FORM_LABEL_STORE_COUNTRY; ?></label>
        <div class="col-sm-8 input-group">
            <?php echo $tplVars['countryString']; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_zone"><?php echo TEXT_FORM_LABEL_STORE_ZONE; ?></label>
        <div class="col-sm-8 input-group" id="store_zone_container">
            <?php echo $tplVars['zoneString']; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-4 control-label" for="store_address"><?php echo TEXT_FORM_LABEL_STORE_ADDRESS; ?></label>
        <div class="col-sm-8 input-group">
          <textarea rows="4" cols="" id="store_address" name="store_address" tabindex="6" placeholder="<?php echo TEXT_EXAMPLE_STOREADDRESS; ?>"><?php echo $tplVars['storeAddress']; ?></textarea>
        </div>
      </div>

      <div class="form-group text-center">
        <input type="submit" class="btn btn-primary btn-lg" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>" tabindex="10">
      </div>

      </form>
  </div>

</div>
</div>


<script>
 $(function() {
    $('#store_country').change(function(e) {
      zcJS.ajax({
        url: "<?php echo zen_href_link($_GET['cmd'], "action=getZones"); ?>",
        data: {id: $(this).val()}
      }).done(function( response ) {
        $('#store_zone_container').html(response.html);
      });
    });
  });
</script>
