<?php
/**
 * PCI Patch for v1.3.x -- to aid in avoiding false-positives thrown by PCI scans
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2025 May 24 Modified in v2.2.0 $
 */
/**
 *
 * Please Note : This file should be placed in includes/extra_configures and will automatically load.
 *
 */

if (isset($_GET['keyword']) && is_array($_GET['keyword'])) {
   $_GET['keyword'] = '';
}
if (isset($_GET['keyword']) && $_GET['keyword'] != '')
{
  $count =  substr_count($_GET['keyword'], '"');
  if ($count == 1)
  {
    if(substr(stripslashes(trim($_GET['keyword'])), 0, 1) == '"')
    {
      $_GET['keyword'] .= '"';
    }
  }
  $_GET['keyword'] = stripslashes($_GET['keyword']);
}
if (isset($_GET['sort']) && strlen($_GET['sort']) > 3) {
  $_GET['sort'] = substr($_GET['sort'], 0, 3);
}
