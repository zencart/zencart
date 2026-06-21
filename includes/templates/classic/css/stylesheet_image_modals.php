<style title="image_modals">
<?php
// This file generates CSS for a few dynamic settings used by image modals
?>
  .image-grid {grid-template-columns: repeat(auto-fill, minmax(<?= (int)$tplSetting->SMALL_IMAGE_WIDTH ?>px, 1fr));}
  .centered-image-medium {max-height: <?= (int)$tplSetting->MEDIUM_IMAGE_HEIGHT ?>px;}
</style>
