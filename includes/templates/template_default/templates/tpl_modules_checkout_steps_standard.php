<?php
/**
 * checkout Steps Template Module
 * 
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in V1.6.0 $
 */
?>
<div id="orderSteps">
<ul>
  <?php foreach ($checkoutStepsList as $checkoutStep) { ?>
  <li <?php echo (($checkoutStep == $checkoutCurrentStep) ? 'class="active"' : ''); ?> >
      <a onclick="return false"><?php echo constant('TEXT_ORDER_STEPS_' . strtoupper($checkoutStep)); ?></a>  
    </li>
  <?php } ?>
</ul>
</div>
