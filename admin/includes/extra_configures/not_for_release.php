<?php
/**
 * Load in any specialized developer and/or unit-testing scripts
 *
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: not_for_release.php 18868 2011-06-08 20:06:38Z wilt $
 */

function twoFactorAuthenticationFunction($params = array())
{
  $result = (TWO_FACTOR_AUTHENTICATION_RESULT == 'true') ? TRUE : FALSE;
  return $result;
}
