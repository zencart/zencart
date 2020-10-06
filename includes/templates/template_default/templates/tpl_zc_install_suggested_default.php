<?php
/**
 * Page Template
 *
 * This page is auto-displayed if the configure.php file cannot be read properly.
 * It is intended simply to recommend clicking on the zc_install link to begin installation.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
$relPath = (file_exists('includes/templates/template_default/images/logo.gif')) ? '' : '../';
$instPath = (file_exists('zc_install/index.php')) ? 'zc_install/index.php' : (file_exists('../zc_install/index.php') ? '../zc_install/index.php' : '');
$docsPath = (file_exists('docs/index.html')) ? 'docs/index.html' : (file_exists('../docs/index.html') ? '../docs/index.html' : '');
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <title>System Setup Required</title>
    <meta content="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="authors" content="The Zen Cart&reg; Team and others">
    <meta name="generator" content="shopping cart program by Zen Cart&reg;, http://www.zen-cart.com">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style type="text/css">
        body {
        	background: #fff;
        	color: #777;
        	font: 16px/1 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        	font-weight: 200;
        	margin: 10px auto;
        	padding: 0 2rem;
        }

        h1 {
        	font-size: 2.25rem;
        	font-weight: 100;
        	color: #000;
        	letter-spacing: 1px;
        	margin: 3rem 0 1.5rem;
        }

        h2 {
        	font-size: 2rem;
        	border-bottom: 1px solid #e3e3e3;
        	font-weight: 300;
        	margin: 2.25rem 0 1rem;
        	padding: 0.5rem 0 1rem;
        }

        h3 {
        	font-size: 1.5rem;
        	font-weight: 400;
        	color: #606060;
        	margin: 1.75rem 0 0.25rem 0;
        }

        h4 {
        	font-size: 1.25rem;
        	font-weight: 300;
        	margin: 1.25rem 0 0.25rem 0;
        	color: maroon;
        	font-variant: small-caps;
        }

        h5, h6 {
        	font-weight: 700;
        }

        h5 {
        	font-size: 1.25rem;
        }

        h6 {
        	font-size: 1rem;
        }

        h5, h6, ol, p, ul {
        	margin: 0 0 1rem 0;
        }

        ul {
        	list-style-type: square;
        }

        ol {
        	list-style-type: upper-roman;
        }

        ol, p, ul {
        	line-height: 1.5;
        }

        ol, ul {
        	padding: 0;
        }

        ol li, ul li {
        	margin-left: 1.125rem;
        }

        ol.noteList {
        	list-style-type: lower-alpha;
        	font-size: small;
        }

        ul.noStyle, ol.noStyle {
        	list-style-type: none;
        }

        a {
        	color: #0080ff;
        	font-weight: 300;
        	text-decoration: none;
        }

        a:visited {
        	color: #0080ff;
        }

        em {
        	color: #444;
        	font-weight: 500;
        	font-style: italic;
        }

        .img-center {
        	display: inline-block;
        	max-width: 100%;
        }

        .no-left-margin {
        	margin-left: 0;
        }

        .errorDetails {
        	color: red;
        	font-weight: 300;
        }

        .add-shadow {
        	-webkit-box-shadow: 4px 10px 41px 0px rgba(161, 161, 161, 0.75);
        	   -moz-box-shadow: 4px 10px 41px 0px rgba(161, 161, 161, 0.75);
        	        box-shadow: 4px 10px 41px 0px rgba(161, 161, 161, 0.75);
        }

        .prime-string {
        	font-size: 2.5rem;
        	font-weight: bold;
        }

        .bold-string {
        	font-weight: bold;
        }

        .small-string, .back-to-top, .appInfo {
        	font-size: small;
        }

        .back-to-top, .appInfo {
        	text-align: center;
        }

        .back-to-top {
        	margin: 2rem 0 2rem 0;
        }

        .back-to-top a {
        	text-decoration: none;
        }

        .appInfo {
        	margin: 4rem 0 2rem 0;
        	color: #888;
        }

        .zenData {
        	margin: 2rem 0 0 0;
        }

        @media screen and (min-width: 1200px) {
        	body {
        		font-size: 1.75rem;
        	}
        	h2 {
        		font-size: 2.25rem;
        	}
        	h1 {
        		font-size: 4.0rem;
        		margin-top: 5rem;
        	}
        }

        @media screen and (max-width: 1199px) {
        	.small-string, .small-string a {
        		font-size: 1.20rem;
        	}
        	.prime-string, .prime-string a {
        		font-size: 1.75rem;
        		font-weight: 500;
        	}
        }

        @media screen and (max-width: 991px) {
        	.alert {
        		padding: 1rem;
        		margin: 1rem 1rem 1rem 1rem;
        	}
        }
    </style>
  </head>

  <body>
  <div class="container">
    <img src="<?php echo $relPath; ?>includes/templates/template_default/images/logo.gif" alt="Zen Cart&reg;" title="Zen Cart&reg;" width="192" height="68" border="0" class="h-img"/>
    <h1>Welcome to Zen Cart<sup>&reg;</sup></h1>
    <div>
      <h2>You are seeing this page for one or more reasons</h2>
      <ol>
        <li>
          This is <strong>your first time</strong> using Zen Cart<sup>&reg;</sup> and you have not yet completed the normal installation procedures.
          <br>
          If this is the case for you,
          <?php if ($instPath) { ?>
            <a href="<?php echo $instPath; ?>">CLICK HERE</a> to begin installation.
          <?php } else { ?>
            you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
          <?php } ?>
          <br><br>
        </li>
        <li>
          This is <strong>not your first time</strong> using Zen Cart<sup>&reg;</sup> and you have previously completed the normal installation procedures.
          <br>
          If this is the case for you, then...
          <br>
          <ul style='list-style-type:square'>
            <li>
              Your <tt><strong>/includes/configure.php</strong></tt> and/or <tt><strong>/admin/includes/configure.php</strong></tt> files contain invalid <em>path information</em> and/or invalid <em>database-connection information</em>.
            <br>
            </li>
            <li>
              If you recently edited your configure.php files for any reason, or perhaps moved your site to a different folder or different server, then you will need to review and update all your settings to the correct values for your server.
              <br>
            </li>
            <li>
              Additionally, if the permissions have been changed on your configure.php files, then perhaps they are too low for the files to be read.
              <br>
            </li>
            <li>
              Or the configure.php files could be missing altogether.
              <br>
            </li>
            <li>
              Or your web hosting provider has recently changed the server's PHP configuration (or upgraded its version) then they may have broken things as well.
              <br>
            </li>
            <li>
              See the <a href="https://docs.zen-cart.com" rel="noopener" target="_blank">Online Documentation</a> area on the Zen Cart<sup>&reg;</sup> website for assistance.
            </li>
          </ul>
        </li>
        <?php if (isset($problemString) && $problemString != '') { ?>
          <br>
          <li>
            Additional <strong>*IMPORTANT*</strong> Details: <span class="errorDetails"><?php echo $problemString; ?></span>
          </li>
        <?php } ?>
      </ol>
    </div>
    <div>
      <h2>To begin installation:</h2>
      <ol>
          <?php if ($docsPath) { ?>
          <li>
            Installation Documentation can be read by <a href="<?php echo $docsPath; ?>">CLICKING HERE</a>
          </li>
        <?php } else { ?>
          <li>
            Installation documentation is normally found in the /docs folder of the Zen Cart&reg; distribution files/zip. You can also find documentation in the <a href="https://docs.zen-cart.com" rel="noopener" target="_blank">Online Help</a>.
          </li>
        <?php } ?>
        <?php if ($instPath) { ?>
          <li>
            Navigate to <a href="<?php echo $instPath; ?>">zc_install/index.php</a> with your web browser.
          </li>
        <?php } else { ?>
          <li>
            You will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
          </li>
        <?php } ?>
        <li>
          Please refer to the <a href="https://docs.zen-cart.com" rel="noopener" target="_blank">Online Help</a> area on the Zen Cart<sup>&reg;</sup> website if you run into difficulties.
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
          and is redistributable under Version 2 of the GNU General Public License.
        <p>
        <p>
          <img src="./docs/osi-certified-120x100.png" alt="O S I Certified">
          <br>
          This software is OSI Certified Open Source Software.
          <br>
          OSI Certified is a certification mark of the Open Source Initiative.
        <p>
        <p class="zenData">
          Copyright 2003 - <?php echo date('Y'); ?> Zen Ventures, LLC
          <br><br>
          Zen Cart&reg;
          <br>
          <a href="https://www.zen-cart.com" rel="noopener" target="_blank">www.zen-cart.com</a>
        </p>
      </div>
    </section> <!-- End footerBlock //-->
  </div>
  </body>
</html>
