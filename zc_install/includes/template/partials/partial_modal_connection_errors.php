<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */
?>

<div id="connection-errors" class="modal fade" data-bs-backdrop="static" tabindex="-1" aria-labelledby="connection-errors-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-4" id="connection-errors-title"><?= TEXT_DATABASE_SETUP_CONNECTION_ERROR_DIALOG_TITLE ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="connection-errors-content" class="modal-body"></div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal"><?= TEXT_CONTINUE ?></button>
            </div>
        </div>
    </div>
</div>
