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
	<meta name="authors" content="The Zen Cart&reg; Team and others" />
	<meta name="generator" content="shopping cart program by Zen Cart&reg;, http://www.zen-cart.com" />
	<meta name="robots" content="noindex, nofollow" />
	<style type="text/css">
		body{
			background:#fff;
			color:#333;
			margin:0;
			font:16px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"
		}
		*{
			box-sizing:border-box
		}
		h1,h2{
			font-weight:700
		}
		h1{
			font-size:2.25rem
		}
		h2{
			font-size:2rem
		}
		h1,h2,ol,p,ul{
			margin:0 0 1rem 0
		}
		ol,p,ul{
			line-height:1.5
		}
		ol,ul{
			padding:0
		}
		ol li,ul li{
			margin-left:1.125rem
		}
		::-moz-focus-inner{
			padding:0;
			border:0
		}
		img{
			border:0
		}
		a{
			color:#0080ff
		}
		a:visited{
			color:#006edb
		}
		.content{
			font-weight:300;
			margin:0 auto;
			max-width:48rem;
			padding:0 2rem
		}
		.content h1{
			font-size:3rem;
			font-weight:200;
			letter-spacing:1px;
			margin:3rem 0 .5rem
		}
		.content h2{
			border-bottom:1px solid #e3e3e3;
			font-weight:100;
			margin:0 0 2.25rem;
			padding:0 0 1.5rem
		}
		@media screen and (min-width:64rem){
			.content{
				font-size:1.25rem
			}
			.content h1{
				font-size:3.5rem;
				margin-top:5rem
			}
			.content h2{
				font-size:2.25rem
			}
		}
	</style>
</head>

<body>
	<div class="content">
		<p>
			<h1>
				<img src="<?php echo $relPath; ?>includes/templates/template_default/images/logo.gif" alt="Zen Cart&reg;" title=" Zen Cart&reg; " width="192" height="68" border="0" /> 
				<br />
				Welcome to Zen Cart<sup>&reg;</sup>
			</h1>
			<br />
			<h2>
				You are seeing this page for one or more reasons:
			</h2>
			<ol>
				<li>
					This is <strong>your first time</strong> using Zen Cart<sup>&reg;</sup> and you have not yet completed the normal installation procedures. If this is the case for you,
<?php if ($instPath) { ?>
					<a href="<?php echo $instPath; ?>">CLICK HERE</a> to begin installation.
<?php } else { ?>
					you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
<?php } ?>
					<br /><br />
				</li>
				<li>
					This is <strong>not your first time</strong> using Zen Cart<sup>&reg;</sup> and you have previously completed the normal installation procedures. If this is the case for you, then...
					<br /><br />
					<ul style='list-style-type:square'>
						<li>
							Your <tt><strong>/includes/configure.php</strong></tt> and/or <tt><strong>/admin/includes/configure.php</strong></tt> files contain invalid <em>path information</em> and/or invalid <em>database-connection information</em>.
							<br />
						</li>
						<li>
							If you recently edited your configure.php files for any reason, or perhaps moved your site to a different folder or different server, then you will need to review and update all your settings to the correct values for your server.
							<br />
						</li>
						<li>
							Additionally, if the permissions have been changed on your configure.php files, then perhaps they are too low for the files to be read.
							<br />
						</li>
						<li>
							Or the configure.php files could be missing altogether.
							<br />
						</li>
						<li>
							Or your web hosting provider has recently changed the server's PHP configuration (or upgraded its version) then they may have broken things as well.
							<br />
						</li>
						<li>
							See the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart<sup>&reg;</sup> website for assistance.
						</li>
					</ul>
				</li>
<?php if (isset($problemString) && $problemString != '') { ?>
				<br />
				<li class="errorDetails">
					Additional <strong>*IMPORTANT*</strong> Details: <?php echo $problemString; ?>
				</li>
<?php } ?>
			</ol>
		</p>
		<br />
		<p>
			<h2>To begin installation:</h2>
			<ol>
<?php if ($docsPath) { ?>
				<li>
					The <a href="<?php echo $docsPath; ?>">Installation Documentation</a> can be read by clicking here: <a href="<?php echo $docsPath; ?>">Documentation</a>
				</li>
<?php } else { ?>
				<li>
					Installation documentation is normally found in the /docs folder of the Zen Cart&reg; distribution files/zip. You can also find documentation in the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQs</a>.
				</li>
<?php } ?>
<?php if ($instPath) { ?>
				<li>
					Run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser.
				</li>
<?php } else { ?>
				<li>
					You will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
				</li>
<?php } ?>
				<li>
					Please refer to the <a href="http://tutorials.zen-cart.com" target="_blank">Online FAQ and Tutorials</a> area on the Zen Cart<sup>&reg;</sup> website if you run into difficulties.
				</li>
			</ol>
		</p>
		<br />
		<p style="text-align: center; font-size: small;">
			Copyright &copy; 2003-<?php echo date('Y'); ?> <a href="http://www.zen-cart.com" target="_blank">Zen Cart</a><sup>&reg;</sup>
		</p>
	</div>
</body>
</html>
