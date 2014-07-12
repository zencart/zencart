<?php
/**
 * dashboard widget  Edit Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
use Zencart\DashboardWidgets\zcWidgetManager;
?>
<div>
<form name="widget-edit" action="#" method="post" class="widget-edit-form">
  <input type="hidden" name="id" value="<?php echo $tplVars['id']; ?>">
  <fieldset>
    <legend><?php echo 'Widget Settings'; ?></legend>
    <div class="row">
      <div class="small-3 columns">
        <label class="inline" for="widget-refresh"><?php echo 'Refresh'; ?></label>
      </div>
      <div class="small-9 columns">
        <?php echo zen_draw_pull_down_menu('widget-refresh', zcWidgetManager::getWidgetTimerSelect($tplVars['id']), $tplVars['widget-refresh']); ?>
      </div>
    </div>
  </fieldset>
<input type="submit" value="<?php echo TEXT_UPDATE; ?>" class="radius button" id="widget-edit-submit-<?php echo $tplVars['id']; ?>">
<a class="secondary button right widget-edit-dismiss" id="widget-edit-dismiss-<?php echo $tplVars['id']; ?>" href="#"><?php echo TEXT_CANCEL; ?></a>
</form>
</div>
<script>
$(function() {
  $('.widget-edit-dismiss').on('click', function() {
    var id = $(this).attr('id');
    zcJS.ajax({
        url: "zcAjaxHandler.php?act=dashboardWidget&method=rebuildWidget",
        data: {'id': id}
      }).done(function( response ) {
        if (response && response.html)
        {
          id = id.replace('widget-edit-dismiss-', '');
          $('#'+ id).find('.widget-body').html(response.html);
        }
      });
  });
  $('.widget-edit-form').submit(function(f) {
    var str = $(this).serialize();
    var id =  $(this).find("input[name='id']").val()
    zcJS.ajax({
        url: "zcAjaxHandler.php?act=dashboardWidget&method=submitWidgetEdit",
        data: {form: str}
      }).done(function( response ) {
        if (response && response.timerKey)
        {
          createWidgetIntervalTimer(response.timerKey, response.timerInterval);
        }
        if (response && response.html)
        {
          $('#'+ id).find('.widget-body').html(response.html);

          $('.widget-update-header').fadeOut(3500, function () {
            $('.widget-update-header').remove();
          });
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