<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
<div id="rowDeleteModal" class="reveal-modal small" data-reveal tabindex="-1">
    <div class="modal-header">
        <a class="close-reveal-modal">×</a>

        <h3><?php echo TEXT_CONFIRM_DELETE; ?></h3>
    </div>
    <div class="modal-body">
        <form>
            <div class="row">
                <div class="small-3 columns">
                    <label class="inline" for="delete_linked"><?php echo TEXT_DELETE_LINKED_ITEMS; ?></label>
                </div>
                <div class="small-9 columns">
                    <input type="checkbox" name="delete_linked" id="delete_linked">
                </div>
            </div>
        </form>
        <p><?php echo TEXT_CONFIRM_DELETE_INFO; ?></p>
    </div>
    <div class="modal-footer">
        <a href="#" id="rowDeleteConfirm" data-item="">
            <button class="radius button"><?php echo TEXT_CONFIRM; ?></button>
        </a>
        <button class="radius button dismiss"><?php echo TEXT_CANCEL; ?></button>
    </div>
</div>
<?php require 'includes/template/javascript/itemRowDeleteHandlerMusicType.php'; ?>
