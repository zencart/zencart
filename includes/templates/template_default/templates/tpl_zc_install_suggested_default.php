<?php
/**
 * Page Template
 *
 * This page is auto-displayed if the configure.php file cannot be read properly. It is intended simply to recommend clicking on the zc_install link to begin installation.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_zc_install_suggested_default.php 19537 2011-09-20 17:14:44Z drbyte $
 */
$relPath = (file_exists('includes/templates/template_default/images/logo.gif')) ? '' : '../';
$instPath = (file_exists('zc_install/index.php')) ? 'zc_install/index.php' : (file_exists('../zc_install/index.php') ? '../zc_install/index.php' : '');
$docsPath = (file_exists('docs/index.html')) ? 'docs/index.html' : (file_exists('../docs/index.html') ? '../docs/index.html' : '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<title>System Setup Required</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="authors" content="The Zen Cart&reg; Team and others" />
<meta name="generator" content="shopping cart program by Zen Cart&reg;, http://www.zen-cart.com" />
<meta name="robots" content="noindex, nofollow" />
<style type="text/css">
<!--
.systemError {color: #FFFFFF}
-->
</style>


</head>

<body style="margin: 20px">
<div style="width: 730px; background-color: #ffffff; margin: auto; padding: 10px; border: 1px solid #cacaca;">
<div>
<img src="<?php echo $relPath; ?>includes/templates/template_default/images/logo.gif" alt="Zen Cart&reg;" title=" Zen Cart&reg; " width="192" height="64" border="0" />
</div>
<h1>Hello. Thank you for loading Zen Cart&reg;.</h1>
<h2>You are seeing this page for one or more reasons:</h2>
<ol>
<li>This is your <strong>first time using Zen Cart&reg;</strong> and you haven't yet completed the normal Installation procedure.<br />
If this is the case for you,
<?php if ($instPath) { ?>
<a href="<?php echo $instPath; ?>">Click here</a> to begin installation.
<?php } else { ?>
you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
<?php } ?>
<br /><br />
</li>
<li>Your <tt><strong>/includes/configure.php</strong></tt> and/or <tt><strong>/admin/includes/configure.php</strong></tt> file contains invalid <em>path information</em> and/or invalid <em>database-connection information</em>.<br />
If you recently edited your configure.php files for any reason, or maybe moved your site to a different folder or different server, then you'll need to review and update all your settings to the correct values for your server.<br />
See the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart&reg; website for assistance.</li>
<?php if (isset($problemString) && $problemString != '') { ?>
<li class="errorDetails">Additional Details: <?php echo $problemString; ?></li>
<?php } ?>
</ol>
<br />
<h2>To begin installation ...</h2>
<ol>
<?php if ($docsPath) { ?>
<li>The <a href="<?php echo $docsPath; ?>">Installation Documentation</a> can be read by clicking here: <a href="<?php echo $docsPath; ?>">Documentation</a></li>
<?php } else { ?>
<li>Installation documentation is normally found in the /docs folder of the Zen Cart&reg; distribution files/zip. You can also find documentation in the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQs</a>.</li>
<?php } ?>
<?php if ($instPath) { ?>
<li>Run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser.</li>
<?php } else { ?>
<li>You will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).</li>
<?php } ?>
<li>The <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart&reg; website will also be of value if you run into difficulties.</li>
</ol>

</div>
    <p style="text-align: center; font-size: small;">Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart&reg;</a></p>
</body></html>
