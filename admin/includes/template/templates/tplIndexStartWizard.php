<?php
/**
 * Admin Start Wizard Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Updated in v1.6.0$
 */

$form_disabled = false;
?>

<div class="content-wrapper no-margin">
  <section class="content-header">
    <h1><?php echo TEXT_HEADING_SETUP_WIZARD; ?></h1>
  </section>
  <?php  if (!zen_is_superuser()) { ?>
    <div class="alert alert-warning" role="alert"><?php echo TEXT_WARNING_SUPERUSER_REQUIRED; ?></div>
  <?php } else { ?>
  <form id="setup-wizard" class="form-horizontal" method="post">
    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
    <input type="hidden" name="action" value="setupWizard">

    <fieldset>
      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_NAME; ?></label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="store_name" value="<?php echo zen_output_string_protected($tplVars['storeName']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STORENAME; ?>">
          </div>
      </div>

        <div class="form-group">
          <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_OWNER; ?></label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="store_owner" value="<?php echo zen_output_string_protected($tplVars['storeOwner']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNER; ?>">
          </div>
        </div>

      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_OWNER_EMAIL; ?></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="store_owner_email" value="<?php echo zen_output_string_protected($tplVars['storeOwnerEmail']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNEREMAIL; ?>">
        </div>
      </div>


      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_COUNTRY; ?></label>
        <div class="col-sm-9">
          <?php echo $tplVars['countryString']; ?>
        </div>
      </div>


      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_ZONE; ?></label>
        <div class="col-sm-9">
          <?php echo $tplVars['zoneString']; ?>
        </div>
      </div>



      <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo TEXT_FORM_LABEL_STORE_ADDRESS; ?></label>
        <div class="col-sm-9">
          <textarea rows="4" cols="" name="store_address" tabindex="6" placeholder="<?php echo TEXT_EXAMPLE_STOREADDRESS; ?>"><?php echo $tplVars['storeAddress']; ?></textarea>
        </div>
      </div>



    </fieldset>
    <button type="submit" class="btn btn-default">Submit</button>
    </form>
    <?php } ?>





</div>

<script>
 $(function() {
    $('#store_country').change(function(e) {
      zcJS.ajax({
        url: "<?php echo zen_admin_href_link($_GET['cmd'], "action=getZones"); ?>",
        data: {id: $(this).val()}
      }).done(function( response ) {
        $('#store_zone_container').html(response.html);
      });
    });
  });
</script>
