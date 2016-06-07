<?php
/**
 * Admin Home Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
//print_r($tplVars);
?>
<section class="content-header">
    <h1><a href="#" class="widget-add">+ Add Widget</a></h1>
</section>

<div class="row">
    <?php for ($column = 0; $column < 3; $column++) { ?>
        <section class="col-lg-4 connectedSortable ui-sortable">
            <?php if (isset($tplVars['widgetInfoList'][$column])) { ?>
                <?php foreach ($tplVars['widgetInfoList'][$column] as $widget) { ?>
                    <?php $tplVars['widget'] = $tplVars['widgets'][$widget['widget_key']]; ?>

                    <div id="<?php echo $tplVars['widgets'][$widget['widget_key']]['widgetBaseId']; ?>" class="flipable">

                        <div class="flip-front box box-solid <?php echo $widget['widget_theme']; ?> sortable">
                            <div class="box-header ui-sortable-handle" style="cursor: move;">
                                <i class="fa <?php echo $widget['widget_icon']; ?>"></i>

                                <h3 class="box-title"><?php echo $tplVars['widgets'][$widget['widget_key']]['widgetTitle']; ?></h3>

                                <div class="pull-right box-tools">
                                    <button class="btn btn-success btn-sm" data-widget="settings" type="button">
                                        <i class="fa fa-wrench"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" data-widget="collapse" type="button">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" data-widget="remove" type="button">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="widget-body">
                                <?php
                                if (file_exists($tplVars['widgets'][$widget['widget_key']]['templateFile'])) {
                                    require($tplVars['widgets'][$widget['widget_key']]['templateFile']);
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                <?php } ?>
            <?php } ?>
        </section>
    <?php } ?>
</div>






<div id="add-widget" class="modal fade " tabindex="-1" role="dialog" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" >Add Widget</h4>
            </div>
            <div class="modal-body add-widget-container">
            </div>
        </div>
    </div>
</div>









<script>

//    $('.dismiss').click(function() {
//        $('a.close-reveal-modal').trigger('click');
//    });




</script>
