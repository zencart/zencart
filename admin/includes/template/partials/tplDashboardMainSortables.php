<?php
/**
 * dashboard main sortables Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Aug 5 20:48:10 2012 -0400 Modified in v1.5.1 $
 */
?>
  <?php for ($column=0; $column<3; $column++) { ?>
    <div class="small-4 columns sortable-column">

    <?php if (isset($tplVars['widgetInfoList'][$column])) { ?>
      <?php foreach ($tplVars['widgetInfoList'][$column] as $widget) { ?>
      <?php $tplVars['widget'] = $tplVars[$widget['widget_key']]; ?>
      <div class="widget-container sortable" id="<?php echo $tplVars[$widget['widget_key']]['widgetBaseId']; ?>">
        <div class="widget-header">
          <h1 class="widget-handle"><?php echo $tplVars[$widget['widget_key']]['widgetTitle']; ?></h1>
          <div class="right">
            <a href="#" class="widget-edit"><i class="icon-pencil"></i></a>
            <a href="#" class="widget-minimize"><i class="icon-down-dir"></i></a>
            <a href="#" class="widget-close"><i class="icon-cancel-squared"></i></a>
            </div>
        </div>
        <div class="widget-body">
        <?php 
           if (file_exists($tplVars[$widget['widget_key']]['templateFile'])) {
              require($tplVars[$widget['widget_key']]['templateFile']); 
           }
        ?>
        </div>
      </div>

      <?php } ?>
    <?php } ?>

    </div>
  <?php }?>
