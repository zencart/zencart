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
    <div class="small-7 columns small-centered">
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
   <div class="panel callout radius">
   <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_NAME; ?></span>
        </div>
        <div class="small-9 columns">
          <input type="text" name="store_name" value="<?php echo zen_output_string_protected($tplVars['storeName']); ?>" autofocus="autofocus" tabindex="1" placeholder="<?php echo TEXT_EXAMPLE_STORENAME; ?>">
        </div></label>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_OWNER; ?></span>
        </div>
        <div class="small-9 columns">
          <input type="text" name="store_owner" value="<?php echo zen_output_string_protected($tplVars['storeOwner']); ?>" tabindex="2" placeholder="<?php echo TEXT_EXAMPLE_STOREOWNER; ?>">
        </div></label>
	  </div>
    </div>
  </div>

  <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_OWNER_EMAIL; ?></span>
        </div>
        <div class="small-9 columns">
          <input type="text" name="store_owner_email" value="<?php echo zen_output_string_protected($tplVars['storeOwnerEmail']); ?>" tabindex="3" placeholder="webmaster@example.com">
        </div></label>
	  </div>
    </div>
  </div>
  
  <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_COUNTRY; ?></span>
        </div>
        <div class="small-9 columns">
          <?php echo $tplVars['countryString']; ?>
        </div></label>
	  </div>
    </div>
  </div>
  
  <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_ZONE; ?></span>
        </div>
        <div class="small-9 columns">
          <?php echo $tplVars['zoneString']; ?>
        </div></label>
	  </div>
    </div>
  </div>
  
  <div class="row">
    <div class="small-8 small-centered columns">
      <div class="row collapse prefix-radius">
        <div class="small-3 columns">
          <label><span class="prefix"><?php echo TEXT_FORM_LABEL_STORE_ADDRESS; ?></span>
        </div>
        <div class="small-9 columns">
          <textarea rows="4" cols="" name="store_address" tabindex="6" placeholder="<?php echo TEXT_EXAMPLE_STOREADDRESS; ?>"><?php echo $tplVars['storeAddress']; ?></textarea>
        </div></label>
	  </div>
    </div>
  </div>
 
  <div class="row">
    <div class="small-8 small-centered columns">
      <input type="submit" class="radius button" id="btnsubmit" name="btnsubmit" value="<?php echo TEXT_SUBMIT; ?>" tabindex="10">
	</div>
  </div>
  </div>
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
