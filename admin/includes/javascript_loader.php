<?php
/**
 * This file is inserted at the start of the body tag, just above the header menu, and loads most of the admin javascript components
 *
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Aug 2017  New in v1.5.6 $
 */
?>
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"><\/script>');</script>
<script>window.jQuery || document.write('<script src="includes/javascript/jquery-1.12.4.min.js"><\/script>');</script>

<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>-->
<script src="includes/javascript/bootstrap.min.js"></script>

<?php if (file_exists(DIR_WS_INCLUDES . 'keepalive_module.php')) require(DIR_WS_INCLUDES . 'keepalive_module.php'); ?>
