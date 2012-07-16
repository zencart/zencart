<?php
/**
 * This page is auto-displayed if an outdated version of PHP version is detected
 *
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_php_version_problem.php 19969 2011-11-08 17:03:26Z drbyte $
 */
$relPath = (file_exists('includes/templates/template_default/images/zen_header_bg.jpg')) ? '' : '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf8">
<title>PHP Version Upgrade Required</title>
<meta name="keywords" content="">
<meta name="description" content="">
<meta http-equiv="imagetoolbar" content="no">
<meta name="authors" content="The Zen Cart&reg; Team and others">
<meta name="generator" content="shopping cart program by Zen Cart&reg;, http://www.zen-cart.com">
<meta name="robots" content="noindex, nofollow">
<style type="text/css">
<!--
body {margin: 10px}
#container {width: 730px; background-color: #ffffff; margin: auto; padding: 10px; border: 1px solid #cacaca;}
div .headerimg {padding:0; width: 730px;}
.systemError {color: red}
-->
</style>
</head>

<body id="pagebody">
<div id="container">
<img src="<?php echo $relPath; ?>includes/templates/template_default/images/zen_header_bg.jpg" alt="Zen Cart&reg;" title=" Zen Cart&reg; " class="headerimg">
<h1>Hello. Thank you for loading Zen Cart&reg;.</h1>
<h2 class="systemError">Unfortunately we've discovered a problem:</h2>
<p class="systemError">The PHP version you are using (<?php echo PHP_VERSION; ?>) is too old, and this version of Zen Cart&reg; cannot be used. You need to upgrade your server to the latest version of PHP.</p>
<p>This version of Zen Cart&reg; requires an absolute minimum of PHP version 5.2.14<br>It is <strong>recommended to use the latest version of PHP 5.3.xx.</strong></p>
<p><em>NOTE: At the time of this release, PHP 5.4 has not yet been extensively tested with this version of Zen Cart&reg;. Check the <a href="www.zen-cart.com">www.zen-cart.com</a> website for the latest version if your server is running PHP 5.4 or newer.</em></p>
<p>The <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart&reg; website is also an important resource.</p>
</div>
<p style="text-align: center; font-size: small;">Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></p>
</body>
</html>
