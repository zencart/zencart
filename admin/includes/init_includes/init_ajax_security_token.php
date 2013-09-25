<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_sessions.php 19956 2011-11-07 15:40:25Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (! isset ( $_SESSION ['ajaxSecurityToken'] ))
{
  $_SESSION ['ajaxSecurityToken'] = md5 ( uniqid ( rand (), true ) );
}
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
  if ((! isset ( $_SESSION ['ajaxSecurityToken'] ) || ! isset ( $_POST ['ajaxSecurityToken'] )) || ($_SESSION ['ajaxSecurityToken'] !== $_POST ['ajaxSecurityToken']))
  {
    header("Status: 403 Forbidden", TRUE, 403);
    echo json_encode(array('error'=>TRUE, 'errorType'=>"SECURITY_TOKEN"));
    exit(1);
  }
}
