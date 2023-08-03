<?php
/**
 * Load in any specialized developer and/or unit-testing scripts
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

function twoFactorAuthenticationFunction($params = array())
{
  $result = (TWO_FACTOR_AUTHENTICATION_RESULT == 'true') ? TRUE : FALSE;
  return $result;
}
