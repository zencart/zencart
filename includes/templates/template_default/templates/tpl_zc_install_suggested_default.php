<?php
/**
 * Page Template
 *
 * This page is auto-displayed if the configure.php file cannot be read properly. It is intended simply to recommend clicking on the zc_install link to begin installation.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sun Jul 15 20:50:58 2012 -0400 Modified in v1.5.1 $
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
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="authors" content="The Zen Cart<sup>&reg;</sup> Team" />
	<meta name="generator" content="shopping cart program by Zen Cart<sup>&reg;</sup>, http://www.zen-cart.com" />
	<meta name="robots" content="noindex, nofollow" />
	<link rel="stylesheet" href="./docs/oxygen.css">
</head>
<body>
	<div>
		<a href="./">
			<img src="includes/templates/template_default/images/logo.gif" alt="Zen Cart&reg;" title=" Zen Cart&reg; " width="192" height="68" border="0" />
		</a>
		<h1>Welcome to Zen Cart&reg;</h1>
		<h2>You see this page for one of the reasons below:</h2>
		<ol>
			<li>
				This is <strong>*A NEW INSTANCE*</strong> of Zen Cart&reg; and you have not yet completed the full installation procedures. If this is the case, please
				<?php if ($instPath) { ?>
				<a href="<?php echo $instPath; ?>">CLICK HERE</a> to begin installation.
				<?php } else { ?> you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
				<?php } ?>
			</li>
			<br>
			<li>
				This is <strong>*AN EXISTING INSTALLATION*</strong> of Zen Cart&reg; and you have previously completed the normal installation procedures. If this is the case, then...
				<br><br>
				<ul style='list-style-type:square'>
					<li>
						Your configure.php files may be missing altogether and need to be recreated.
					</li>
					<li>
						Your web hosting provider may have changed the PHP configuration or upgraded the PHP version on the server and either of these may have broken things.
					</li>
					<li>
						Your configure.php files in the <tt><strong>/includes/</strong></tt> and/or <tt><strong>/admin/includes/</strong></tt> folders may contain invalid <em>path information</em> and/or invalid <em>database connection information</em>.
					</li>
					<li>
						You have edited your configure.php files, or perhaps moved your site to a different folder or different server. Please review and update all your settings to the reflect correct values.
					</li>
					<li>
						Permissions have been changed on your configure.php files and these may not allow the files to be read.
					</li>
				</ul>
			</li>
		</ol>
	</div>
	<?php if (isset($problemString) && $problemString != '') { ?>
	<br>
	<div>
		Additional <strong>*IMPORTANT*</strong> Details:
		<br>
		<?php echo '<strong>"</strong><span class="errorDetails">' . $problemString . '</span><strong>"</strong>'; ?>
	</div>
	<?php } ?>
	<br>
	<div>
		<h2>To Begin Installation</h2>
		<ol>
			<?php if ($instPath) { ?>
			<li>
				Run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser.
			</li>
			<?php } else { ?>
			<li>
				You will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
			</li>
			<?php } ?>
			<?php if ($docsPath) { ?>
			<li>
				<a href="<?php echo $docsPath; ?>">CLICK HERE</a> to read the installation documentation
			</li>
			<?php } else { ?>
			<li>
				Installation documentation is normally found in the /docs folder of the Zen Cart&reg; distribution files/zip. You can also find documentation in the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQs</a>.
			</li>
			<?php } ?>
			<li>
				Please refer to the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart&reg; website if you run into difficulties.
			</li>
		</ol>
	</div>
	<br>
	<div class="h-text-center small-string">
		<em>
			Copyright 2003 - 2018 Zen Ventures, LLC
			<br /><br />
			Zen Cart<sup>&reg;</sup> 
			<br />
			<a href="https://www.zen-cart.com" target="_blank">www.zen-cart.com</a>
		</em>
	</div>
</body>
</html>
