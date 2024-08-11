<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 */
?>

<div id="install-errors" class="modal fade" data-bs-backdrop="static" tabindex="-1" aria-labelledby="install-errors-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-4" id="install-errors-title"><?= TEXT_DATABASE_SETUP_CONNECTION_ERROR_DIALOG_TITLE ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="install-errors-content" class="modal-body"></div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal"><?= TEXT_CONTINUE ?></button>
            </div>
        </div>
    </div>
</div>
