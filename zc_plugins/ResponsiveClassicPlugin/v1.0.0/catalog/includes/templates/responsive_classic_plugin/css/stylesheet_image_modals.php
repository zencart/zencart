<style title="image_modals">
<?php
/**
 * This file generates CSS for a few dynamic settings used by image modals
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2025 Mar 16 Modified in v2.2.0 $
 */
?>
  .image-grid {grid-template-columns: repeat(auto-fill, minmax(<?php echo (int)SMALL_IMAGE_WIDTH; ?>px, 1fr));}
  .centered-image-medium {max-height: <?php echo (int)MEDIUM_IMAGE_HEIGHT; ?>px;}
</style>
