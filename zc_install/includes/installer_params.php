<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: installer_params.php 18819 2011-05-31 20:25:53Z drbyte $
 */

/**
 * Runtime Parameters used by browser interface
 */
//  $session_save_path = (@ini_get('session.save_path') && is_writable(ini_get('session.save_path')) ) ? ini_get('session.save_path') : realpath('../cache');
  $session_save_path = (is_writable(realpath('../cache')) ) ? realpath('../cache') : ini_get('session.save_path');
  define('SESSION_WRITE_DIRECTORY', $session_save_path);
  define('DEBUG_LOG_FOLDER', realpath('../cache'));

  // Set the following to TRUE if having problems (blank pages, etc). Best to leave at FALSE for normal use.
  define('STRICT_ERROR_REPORTING', FALSE);


  // optionally set this to 'latin1':
  define('DB_CHARSET', 'utf8');

  // optionally uncomment the following line if choosing 'utf8' or 'latin1' above are causing problems:
  // define('IGNORE_DB_CHARSET', TRUE);

