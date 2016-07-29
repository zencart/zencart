<?php
/**
 * dashboard widget Installable Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Aug 5 20:48:10 2012 -0400 Modified in v1.5.1 $
 */
?>
<?php if ($tplVars['flagHasWidgets']) { ?>
    <?php foreach ($tplVars['widgets'] as $widget) { ?>
        <div class="row">
            <p class="col-sm-offset-3"><?php echo $widget['widget_name']; ?></p>
            <p class="col-sm-offset-3">
                <?php echo(defined($widget['widget_description']) ? constant($widget['widget_description']) : $widget['widget_description']); ?>
            </p>
            <a class="col-sm-offset-3 button tiny add-widget-button" id="add-widget-<?php echo $widget['widget_key']; ?>" href="#">Add Widget</a>
        </div>
        <br class="clear">
    <?php } ?>
<?php } else { ?>
<p><?php echo TEXT_NO_WIDGETS_TO_INSTALL; ?>
<p>
<?php } ?>
<script>
$(function() {

  $('.add-widget-button').click(function(e) {
    var id = $(this).attr('id');
    zcJS.ajax({
      url: "zcAjaxHandler.php?act=dashboardWidget&method=addWidget",
      data: {'id': id}
    }).done(function( response ) {
        window.location.replace("<?php echo zen_admin_href_link(FILENAME_DEFAULT);?>");
    });

  });
});
</script>
