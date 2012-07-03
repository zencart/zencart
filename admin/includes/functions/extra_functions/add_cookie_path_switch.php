<?php
/**
 * @package patches
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: add_cookie_path_switch.php 18695 2011-05-04 05:24:19Z drbyte $
 */
if (!defined('SESSION_USE_ROOT_COOKIE_PATH') || !defined('SESSION_ADD_PERIOD_PREFIX'))
{
  $sql = "SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " 
          WHERE configuration_group_title = 'Sessions'";
  $result = $db->execute($sql);
  if ($result->RecordCount() > 0)
  {
    $id = $result->fields['configuration_group_id'];
  } else 
  {
    $id = 15;    
  }
  if (!defined('SESSION_USE_ROOT_COOKIE_PATH'))
  {
    $sql = "INSERT INTO " . TABLE_CONFIGURATION . " 
           SET configuration_key = 'SESSION_USE_ROOT_COOKIE_PATH',
                sort_order =  '999', 
                configuration_title = 'Use root path for cookie path', 
                configuration_value = 'False',
                configuration_description = 'Normally Zen Cart will use the directory that a store resides in as the cookie path. This can cause problems with some servers. This setting allows you to set the cookie path to the root of the server, rather than the store directory. It should only be used if you have problems with sessions. <strong>Default Value = False</strong><br /><br /><strong>Changing this setting may mean you have problems logging into your admin, you should clear your browser cookies to overcome this.</strong>' ,
                configuration_group_id = " . (int)$id . ",
                set_function = 'zen_cfg_select_option(array(\'True\', \'False\'), '
                 " ;
    $result = $db->execute($sql);
  }
  if (!defined('SESSION_ADD_PERIOD_PREFIX'))
  {
    $sql = "INSERT INTO " . TABLE_CONFIGURATION . " 
           SET configuration_key = 'SESSION_ADD_PERIOD_PREFIX',
                sort_order =  '999', 
                configuration_title = 'Add period prefix to cookie domain', 
                configuration_value = 'True',
                configuration_description = 'Normally Zen Cart will add a period prefix to the cookie domain, e.g.  .www.mydomain.com. This can sometimes cause problems with some server configurations. If you are having session problems you may want to try setting this to False. <strong>Default Value = True</strong>',
                configuration_group_id = " . (int)$id . ",
                set_function = 'zen_cfg_select_option(array(\'True\', \'False\'), '
                 " ;
    $result = $db->execute($sql);
  }
}