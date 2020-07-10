<?php
/**
 * Page Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 21:59:27 2015 -0400 Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="sslCheck">    

<h1 id="sslCheckHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="sslCheckMainContent" class="content"><?php echo TEXT_INFORMATION; ?></div>

<h2 id="sslCheckSubHeading"><?php echo BOX_INFORMATION_HEADING; ?></h2>
<div id="sslCheckSecondaryContent" class="content"><?php echo BOX_INFORMATION; ?></div>

<p  id="sslCheckContent2" class="content"><?php echo TEXT_INFORMATION_2; ?></p>
<p  id="sslCheckContent3" class="content"><?php echo TEXT_INFORMATION_3; ?></p>
<p  id="sslCheckContent4" class="content"><?php echo TEXT_INFORMATION_4; ?></p>
<p  id="sslCheckContent5" class="content"><?php echo TEXT_INFORMATION_5; ?></p>

<div class="buttonRow forward"><?php echo '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></div>
</div>