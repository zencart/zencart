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
  if (isset($tplVars['widget']['content']) && sizeof($tplVars['widget']['content']) > 0) {
    foreach ($tplVars['widget']['content'] as $i=>$entry) {
    $evenodd = ($i%2) ? 'odd' : 'even';
      $params = array($evenodd,$entry['text'],$entry['value']);
      echo vsprintf('<div class="widget-row widget-row-%s"><span>%s</span><span class="right">%s</span></div>', $params);
    }
  } else { 
    echo "&nbsp;"; 
  }
