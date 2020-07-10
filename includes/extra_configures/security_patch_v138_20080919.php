<?php
/** 
 * Security Patch v1.3.8 20080919
 * 
 * @package initSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: security_patch_v138_20080919.php 15882 2010-04-11 16:37:54Z wilt $
 */
/**
 * Security Patch
 * 
 * Multiple Vulnerabilities
 * 
 * SQL Injection - $_POST['products_id'] 
 * SQL Injection - $_POST['id']
 * 
 * Please Note : This file should be placed in includes/extra_configures and will automatically load.
 *  
 */
if (isset($_POST['id']) && is_array($_POST['id']) && count($_POST['id']) > 0)
{
  $_POST['id'] = securityPatchSanitizePostVariableId($_POST['id']);
}
if (isset($_POST['products_id']) && is_array($_POST['products_id']) && count($_POST['products_id']) > 0)
{
  $_POST['products_id'] = securityPatchSanitizePostVariableProductsId($_POST['products_id']);
}
if (isset($_POST['notify']) && is_array($_POST['notify']) && count($_POST['notify']) > 0)
{
  $_POST['notify'] = securityPatchSanitizePostVariableProductsId($_POST['notify']);
}
function securityPatchSanitizePostVariableId ($arrayToSanitize)
{
  foreach ($arrayToSanitize as $key => $variableToSanitize)
  {
    {
      if (is_integer($key))
      {
        if (is_array($arrayToSanitize[$key]))
        {
          $arrayToSanitize[$key] = securityPatchSanitizePostVariableId($arrayToSanitize[$key]);
        }
        else 
        {
          $arrayToSanitize[$key] = (int) $variableToSanitize;
        }
      }
    }
    if (preg_replace('/[0-9a-zA-z:_]/', '', $key) != '')
      unset($arrayToSanitize[$key]);
  }
  return $arrayToSanitize;
}
function securityPatchSanitizePostVariableProductsId ($arrayToSanitize)
{
  foreach ($arrayToSanitize as $key => $variableToSanitize)
  {
    {
      $arrayToSanitize[$key] = preg_replace('/[^0-9a-fA-F:.]/', '', $variableToSanitize);
    }
    if (preg_replace('/[0-9a-zA-z_:.]/', '', $key) != '')
      unset($arrayToSanitize[$key]);
  }
  return $arrayToSanitize;
}
  