<?php
/**
 * compatibility functions
 *
 * @package functions
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: compatibility.php 2618 2005-12-20 00:35:47Z drbyte $
 */


// This file is empty in v1.5.2

/**
 * Function performed same action as zen_output_string_protected therefore is being deprecated.
 * @param $string - string to sanitize
 * @return string
 */
function zen_db_output($string)
{
    return zen_output_string_protected($string);
}
