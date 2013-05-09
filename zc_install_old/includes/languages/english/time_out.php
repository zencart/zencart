<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: time_out.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - Time Out');
  define('TEXT_MAIN','<h2>Problem Detected</h2><h3>Sorry, a problem has been detected.</h3>
<p>This installer needs to be able to start a PHP session in order to complete installation.</p>
<p><strong>Possible causes of this page being displayed include:</strong>
<ul>
<li><h3>Perhaps a timeout has occurred</h3>If you left the installation unattended more than 20 minutes, then the information you entered on previous screens is no longer available.  In this case, you will need to begin the installation process again. <br />
<br />
Please do not leave the installation process unattended for long lengths of time. Installation should only take 5 minutes under normal circumstances.<br /><br /></li>
<li><h3>The server has no place to write its session files.</h3>If you have not already made the "cache" folder writable, please do so now so the installer can continue. Details on making the files writable can be found in the <a href="../docs/index.html" target="_blank">Installation Instructions</a> or in the <a href="http://tutorials.zen-cart.com/index.php?article=9" target="_blank">online FAQs</a>.<br /><br /></li>
<li><h3>PHP sessions might not be functional on your server</h3>In order to use Zen Cart&reg;, you will need to be able to use the "session" capabilities of PHP. It could be that your server configuration is not currently allowing PHP sessions to start and interact properly.  Also, if you have session cookies disabled in your browser, you will not be able to use PHP session support. Please turn off cookie-blocking tools in your browser and firewall as a precaution. <br /><br />You may need to talk to your webserver administrator for assistance in ensuring that PHP sessions can be configured and used on your site.<br /><br /></li>
</ul>
</p>');
  define('START_INSTALL', 'Start Installation');
