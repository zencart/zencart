<?php
/**
 * dashboard widget Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Aug 5 20:48:10 2012 -0400 Modified in v1.5.1 $
 */
?>
<?php
  if (sizeof($tplVars['widget']['content']) > 0) { 
    foreach ($tplVars['widget']['content'] as $entry) { 
?>
      <div class="widget-row"><span><?php echo $entry['text']; ?></span><span class="right"><?php echo $entry['value']; ?></span></div>
<?php
    }
  }