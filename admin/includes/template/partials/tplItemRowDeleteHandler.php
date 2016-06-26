<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div class="modal fade" tabindex="-1" role="dialog" id="rowDeleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo TEXT_CONFIRM_DELETE; ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo TEXT_CONFIRM_DELETE_TEXT; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo TEXT_CANCEL; ?></button>
                <button type="button" class="btn btn-primary" id="rowDeleteConfirm"><?php echo TEXT_CONFIRM; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php require 'includes/template/javascript/itemRowDeleteHandler.php'; ?>
