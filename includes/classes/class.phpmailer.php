<?php
/**
 * PHPMailer 6 SPL autoloader.
 *
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer SPL autoloader.
 * @param string $classname The name of the class to load
 */
function PHPMailerAutoload($classname)
{
    // bust namespace limitations
    $classname = substr($classname, strrpos($classname, '\\')+1);

    $filename = DIR_FS_CATALOG.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'PHPMailer'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.$classname.'.php';
    if (is_readable($filename)) {
        require $filename;
    }
}

if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
    spl_autoload_register('PHPMailerAutoload', true, true);
} else {
    spl_autoload_register('PHPMailerAutoload');
}
