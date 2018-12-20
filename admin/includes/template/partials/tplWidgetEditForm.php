<?php
/**
 * dashboard widget  Edit Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php echo TITLE_MODAL_DASHBOARD_WIDGETS_SETTINGS; ?></h4>
</div>
<div class="modal-body">
    <form name="widget-edit" action="#" method="post" class="widget-edit-form">
        <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
        <input type="hidden" name="widget_key" value="<?php echo $tplVars['widget']['info']['widget_key']; ?>">
        <?php foreach($tplVars['widget']['settings'] as $setting) { ?>
            <div class="form-group">
                <label for=""><?php echo $setting['title']; ?>
                    <?php require('includes/template/partials/settingTypes/' . $setting['setting_type'] . '.php'); ?>
                </label>
            </div>
        <?php } ?>
        <input type="submit" value="<?php echo TEXT_UPDATE; ?>" class="radius button" id="widget-edit-submit-">
        <a class="secondary button right widget-edit-dismiss" data-dismiss="modal" aria-label="Close"
           href="#"><?php echo TEXT_CANCEL; ?></a>
    </form>
</div>


<script>
$(function() {
  $('.widget-edit-form').submit(function(f) {
    var str = $(this).serialize()
    var id =  $(this).find("input[name='id']").val()
    zcJS.ajax({
        url: "zcAjaxHandler.php?act=dashboardWidget&method=submitWidgetEdit",
        data: str
      }).done(function( response ) {
        if (response && !response.errors)
        {
          $('#widget-settings').modal('hide');
            window.location.replace("<?php echo zen_admin_href_link(FILENAME_DEFAULT);?>");
        } else if (response && response.error && response.errorType == 'FORM_VALIDATION')
        {
          handleFormValidationErrors(response.errorList);
        }
      });
    f.preventDefault();
  });
});
function handleFormValidationErrors(errorList)
{
}
</script>
