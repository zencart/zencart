<?php
/**
 * disabled-upcoming products auto_loader to execute the operations.
 *
 * @copyright 2018
 * @license http://www.zen-cart.com/License/2_0.txt GNU Public License V2.0
 * @author mc12345678
 **/
/**
 *Load just before the other special functions that may include this product.
 */

$autoLoadConfig[149][] = array(
                                      'autoType'=>'init_script',
                                      'loadFile'=>'init_special_funcs_disabled_upcoming.php',
                                     );
