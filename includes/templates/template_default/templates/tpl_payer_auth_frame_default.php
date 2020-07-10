<?php
/**
 * template for 3d-secure iframe
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2005 CardinalCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_payer_auth_frame_default.php 15817 2010-04-04 22:13:38Z drbyte $
 */
?>
<div>
  <div class="bold"><p><?php echo TEXT_3DS_PAYER_AUTH_FRAME_TITLE_MESSAGE; ?></p></div>
  <div class="forward"><?php echo zen_image(DIR_WS_IMAGES.'3ds/vbv.gif');?></div>
  <div class="forward"><?php echo zen_image(DIR_WS_IMAGES.'3ds/mcsc.gif');?></div>
</div>

<iframe name="auth_frame" id="authFrame" class="authFrame" src="<?php echo $_SESSION['3Dsecure_auth_url'] ?>" frameborder="0" width="500" height="500" scrolling="no" style="align: center;"></iframe>
