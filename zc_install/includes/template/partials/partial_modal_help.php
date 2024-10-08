<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */

?>

<div id="modal-help" class="modal fade" tabindex="-1" aria-labelledby="modal-help-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" id="modal-help-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-4" id="modal-help-title"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-help-content" class="modal-body"></div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal"><?= TEXT_CONTINUE ?></button>
            </div>
        </div>
    </div>
</div>
