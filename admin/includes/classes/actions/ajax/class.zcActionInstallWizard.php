<?php
/**
 * zcActionInstallWizard Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version 
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcActionInstallWizard Class
 *
 * @package classes
 */
class zcActionInstallWizard extends zcActionAjaxBase
{
  public function getZonesExecute()
  {
    if (isset($_POST['id']))
    {
      $options = zen_get_country_zones((int)$_POST['id']);
      array_unshift($options, array('id'=>0, 'text'=>TEXT_NONE));
      $html = zen_draw_pull_down_menu('store_zone', $options, -1, 'id="store_zone"');
      $this->response = array('html'=>$html);
    }
  }
}