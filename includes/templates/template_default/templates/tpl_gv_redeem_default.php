<?php
/**
 * Page Template
 *
 * Display information related to GV redemption (could be redemption details, or an error message)
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Oct 30 Modified in v1.5.7a $
 */
?>
<div class="centerColumn" id="gvRedeemDefault">

<h1 id="gvRedeemDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="gvRedeemDefaultMessage" class="content"><?php echo sprintf($message, $_GET['gv_no']); ?></div>

<div id="gvRedeemDefaultMainContent" class="content"><?php echo TEXT_INFORMATION; ?></div>

<?php
$link = zen_href_link(FILENAME_DEFAULT);
if (isset($_GET['goback']) && $_GET['goback'] == 'true') $link = zen_href_link(FILENAME_GV_FAQ);
?>

<div class="buttonRow forward"><?php echo '<a href="' . $link . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></div>

</div>
