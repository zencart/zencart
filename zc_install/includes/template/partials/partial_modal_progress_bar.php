<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2024 Jan 20 Modified in v2.0.0-alpha1 $
 */

?>

<div id="progress-bar-dialog" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="progress-bar-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-4" id="progress-bar-title"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="progress-bar-container" class="modal-body">
                <div id="progress-meter" class="progress align-items-center" role="progressbar" style="--bs-progress-height: 32px; --bs-progress-bar-bg: #198754; --bs-progress-bg: #dbcbab; --bs-progress-font-size: 1.25rem">
                    <div id="progress-meter-text" class="progress-bar text-light overflow-visible" style="0%"></div>
                </div>
                <div id="progress-container">
                    <div id="progress-info" class="progress-detail"></div>
                </div>
            </div>
        </div>
    </div>
</div>

