<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div id="rowDeleteModal" class="reveal-modal small" data-reveal tabindex="-1">
    <div class="modal-header">
        <a class="close-reveal-modal">×</a>

        <h3>Confirm Delete</h3>
    </div>
    <div class="modal-body">
        <p> Are you sure you want to delete this item </p>
    </div>
    <div class="modal-footer">
        <a href="#" id="rowDeleteConfirm" data-item="">
            <button class="radius button">Confirm</button>
        </a>
        <button class="radius button dismiss">Cancel</button>
    </div>
</div>
<?php require 'includes/template/javascript/itemRowDeleteHandler.php'; ?>
