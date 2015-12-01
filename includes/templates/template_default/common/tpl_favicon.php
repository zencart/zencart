<?php
/**
 * Common Template
 *
 * outputs the favicon components
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte  New in v1.6.0 $
 */
?>
<?php
/**
 * Use this template to set up your favicon markup
 * At a minimum, you should have two:
 *   1. a 32x32 favicon.ico file in the root of your site, to handle all the generic basic needs. (32x32 will automatically downscale to 16x16 for browsers that need that)
 *   2. a 128x128 favicon.png file so that more advanced browsers can use this for various other needs. They will downscale it as needed.
 *
 * Then consider some larger images of various sizes for Apple (180x180) and Android (192x192) touch devices.
 *
 * Worthwhile related reading:
 * - https://mathiasbynens.be/notes/touch-icons
 * - http://www.netmagazine.com/features/create-perfect-favicon
 * - http://www.jonathantneal.com/blog/understand-the-favicon/
 * - http://msdn.microsoft.com/en-us/library/ms537656(v=vs.85).aspx
 * - http://developer.apple.com/library/safari/#documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
 */
?>
<?php
/**
 * This section is for traditional FAVICON.ICO or FAVICON.PNG files
 * NOTE: IE10 doesn't support PNG icons nor the conditional code, but you can overcome this by simply using a /favicon.ico file in the root of your site ... which is how favicons first worked
 * - try to keep the files under 20k in size
 * - favicon .ICO files are typically 32x32 (can contain more sizes in same file, but not really needed)
 * - favicon .PNG files are usually 96x96
 * While multiple sizes can be specified, PNG images compress well so a single 96x96 or 128x128 file should suffice
 */
?>
    <link rel="icon" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>favicon.png">
    <!--[if IE]><link rel="shortcut icon" href="favicon.ico"><![endif]-->
<?php
/**
 * This section is for Apple Touch Icons -- and will be used on iOS devices for shortcut icons
 * Android devices will use these <link> references too
 *
 * Suggested use: target 57x57 and 180x180. Others are optional.
 * If you use multiple sizes, start with largest sizes first, then progressively smaller. However, the fewer you use, the less traffic (page load size) will be consumed.
 */
?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>apple-touch-icon.png">
    <link rel="icon" sizes="192x192" href="<?php echo DIR_WS_TEMPLATE_ICONS; ?>touch-icon-192x192.png">
<?php
/**
 * For IE10 / Windows 8, you can set the tile color and image (best is a transparent 144x144 PNG)
 *
 * Uncomment the following lines if you choose to use them
 */
// define('FAVICON_MS_TILE_IMAGE', DIR_WS_TEMPLATE_ICONS . 'ms-tile-image144x144.png');
// define('FAVICON_MS_TILE_COLOR', '#DDFFAA');
?>
<?php if (defined('FAVICON_MS_TILE_IMAGE')) { ?>
    <meta name="msapplication-TileColor" content="<?php echo FAVICON_MS_TILE_COLOR; ?>">
    <meta name="msapplication-TileImage" content="<?php echo FAVICON_MS_TILE_IMAGE; ?>">
<?php } ?>

