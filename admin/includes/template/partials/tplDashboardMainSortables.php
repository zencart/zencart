<?php
/**
 * dashboard main sortables Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<?php for ($column = 0; $column < 3; $column++) { ?>
    <div class="small-4 columns sortable-column">

        <?php if (isset($tplVars['widgetInfoList'][$column])) { ?>
            <?php foreach ($tplVars['widgetInfoList'][$column] as $widget) { ?>
                <?php $tplVars['widget'] = $tplVars['widgets'][$widget['widget_key']]; ?>
                <div class="widget-container sortable" id="<?php echo $tplVars['widgets'][$widget['widget_key']]['widgetBaseId']; ?>">
                    <div class="widget-header">
                        <h1 class="widget-handle"><?php echo $tplVars['widgets'][$widget['widget_key']]['widgetTitle']; ?></h1>
                        <div class="right">
                            <a href="#" class="widget-edit" title="<?php echo IMAGE_EDIT; ?>">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="#" class="widget-minimize" title="<?php echo IMAGE_COLLAPSE; ?>">
                                <i class="fa fa-toggle-down"></i>
                            </a>
                            <a href="#" class="widget-close" title="<?php echo IMAGE_DELETE; ?>"><i class="fa fa-trash"></i></a>
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
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>
