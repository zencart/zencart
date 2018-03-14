<?php
/**
 * Page Template
 *
 * This page is auto-displayed if the configure.php file cannot be read properly. It is intended simply to recommend clicking on the zc_install link to begin installation.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.5.6 $
 */
$relPath = (file_exists('includes/templates/template_default/images/logo.gif')) ? '' : '../';
$instPath = (file_exists('zc_install/index.php')) ? 'zc_install/index.php' : (file_exists('../zc_install/index.php') ? '../zc_install/index.php' : '');
$docsPath = (file_exists('docs/index.html')) ? 'docs/index.html' : (file_exists('../docs/index.html') ? '../docs/index.html' : '');
?>
<!DOCTYPE html>
<head>
	<title>System Setup Required</title>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Content-Type" content="text/html">
	<meta http-equiv="imagetoolbar" content="no">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta name="authors" content="The Zen Cart&reg; Team">
	<meta name="generator" content="shopping cart program by Zen Cart&reg;, https://www.zen-cart.com">
	<meta name="robots" content="noindex, nofollow">
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
				This is <em>*A NEW INSTANCE*</em> of Zen Cart&reg; and you have not yet completed the full installation procedures. If this is the case, please
				<?php if ($instPath) { ?>
				<a href="<?php echo $instPath; ?>">CLICK HERE</a> to begin installation.
				<?php } else { ?> you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
				<?php } ?>
			</li>
			<br>
			<li>
				This is <em>*AN EXISTING INSTALLATION*</em> of Zen Cart&reg; and you have previously completed the normal installation procedures. If this is the case, then...
				<br><br>
				<ul style='list-style-type:square'>
					<li>
						Your configure.php files may be missing altogether and need to be recreated.
					</li>
					<li>
						Your web hosting provider may have changed the PHP configuration or upgraded the PHP version on the server and either of these may have broken things.
					</li>
					<li>
						Your configure.php files in the <em>/includes/</em> and/or <em>/admin/includes/</em> folders may contain invalid <em>path information</em> and/or invalid <em>database connection information</em>.
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
		Additional <em>*IMPORTANT*</em> Details:
		<br>
		<?php echo '<em>"</em><span class="errorDetails">' . $problemString . '</span><em>"</em>'; ?>
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
	
	<section id="footerBlock">
		<div class="appInfo">
			<p>
				Zen Cart&reg; is derived from: Copyright 2003 osCommerce
				<br><br>
				This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
				<br>
				without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE
				<br>
				and is redistributable under the GNU General Public License.
			<p>
			<p>
				<img src="./docs/osi-certified-120x100.png" alt="O S I Certified">
				<br>
				This software is OSI Certified Open Source Software.
				<br>
				OSI Certified is a certification mark of the Open Source Initiative.
			<p>
			<p class="zenData">
				Copyright 2003 - 2018 Zen Ventures, LLC
				<br><br>
				Zen Cart&reg; 
				<br>
				<a href="https://www.zen-cart.com" target="_blank">www.zen-cart.com</a>
			</p>
		</div>
	</section> <!-- End footerBlock //-->
	
</body>
</html>
