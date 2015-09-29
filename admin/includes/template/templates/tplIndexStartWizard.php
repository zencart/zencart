<?php
/**
 * Admin Start Wizard Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Updated in v1.6.0$
 */
?>
<style>
/** @TODO - replace this with a Zurb Foundation class */
.form-horizontal input, select
{
  font-size: .8em;
}
.invalid {
  color: #FF2222
}
</style>
<div class="content-fluid">
  <div class="row">
    <div class="small-12 columns small-centered">
  <h1><?php echo TEXT_HEADING_SETUP_WIZARD; ?></h1>
<?php
  if (!zen_is_superuser())
  {
?>
  <div class="alert alert-error"><?php echo TEXT_WARNING_SUPERUSER_REQUIRED; ?></div>
  </div>
<?php
  } else {
  ?>
   <form id="setup-wizard" class="form-horizontal" method="post">
   <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
   <input type="hidden" name="action" value="setupWizard">
   <fieldset>
   <legend><?php echo TEXT_FORM_LEGEND_REQUIRED_SETUP; ?></legend>
  <div class="row">
  <div class="small-3 columns">
  <label class="inline" for="store_name"><?php echo TEXT_FORM_LABEL_STORE_NAME; ?></label>
  </div>
  <div class="small-9 columns">
  <input type="text" id="store_name" name="store_name" value="<?php echo zen_output_string_protected($tplVars['storeName']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STORENAME; ?>">
  </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_owner"><?php echo TEXT_FORM_LABEL_STORE_OWNER; ?></label>
   </div>
  <div class="small-9 columns">
    <input type="text" id="store_owner" name="store_owner" value="<?php echo zen_output_string_protected($tplVars['storeOwner']); ?>" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNER; ?>">
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_owner_email"><?php echo TEXT_FORM_LABEL_STORE_OWNER_EMAIL; ?></label>
   </div>
  <div class="small-9 columns">
   <input type="text" id="store_owner_email" name="store_owner_email" value="<?php echo zen_output_string_protected($tplVars['storeOwnerEmail']); ?>" tabindex="3" placeholder="webmaster@example.com">
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_country"><?php echo TEXT_FORM_LABEL_STORE_COUNTRY; ?></label>
   </div>
  <div class="small-9 columns">
   <?php echo $tplVars['countryString']; ?>
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_zone"><?php echo TEXT_FORM_LABEL_STORE_ZONE; ?></label>
   </div>
  <div class="small-9 columns" id="store_zone_container">
   <?php echo $tplVars['zoneString']; ?>
   </div>
   </div>

  <div class="row">
  <div class="small-3 columns">
   <label class="inline" for="store_address"><?php echo TEXT_FORM_LABEL_STORE_ADDRESS; ?></label>
   </div>
  <div class="small-9 columns">
   <textarea rows="" cols=""  id="store_address" name="store_address" tabindex="6" placeholder="<?php echo TEXT_EXAMPLE_STOREADDRESS; ?>"><?php echo $tplVars['storeAddress']; ?></textarea>
   </div>
   </div>

   <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>" tabindex="10">
   </fieldset>
   </form>
<script>
   $(function()
       {
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
<?php
  }
?>
  </div>
</div>
