<?php
/**
 * @package install
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 *
 */

/**
 * Optionally set a MySQL mode during installation
 * Ref: https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
 */
define('DB_MYSQL_MODE', 'TRADITIONAL');
//STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY,NO_AUTO_VALUE_ON_ZERO
