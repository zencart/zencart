<?php
/**
 * dashboard widget Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<?php
  if (isset($tplVars['widget']['content']) && sizeof($tplVars['widget']['content']) > 0) {
    foreach ($tplVars['widget']['content'] as $entry) {
?>
      <div class="row widget-row">
          <?php if (!isset($entry['fullrow'])) { ?>
          <div class="col-lg-8"><?php echo $entry['text']; ?></div>
          <div class="col-lg-4  text-right"><?php echo $entry['value']; ?></div>
          <?php } else { ?>
          <div class="col-lg-12"><?php echo $entry['text']; ?></div>
          <?php } ?>
      </div>
<?php
    }
  } else { 
    echo "&nbsp;"; 
  }
