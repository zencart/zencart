<style>
<?php
// This file generates CSS for a few dynamic settings used by image modals
?>
  .image-grid {grid-template-columns: repeat(auto-fill, minmax(<?php echo (int)SMALL_IMAGE_WIDTH; ?>px, 1fr));}
  .centered-image-medium {max-height: <?php echo (int)MEDIUM_IMAGE_HEIGHT; ?>px;}
</style>

