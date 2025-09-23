<?php
declare(strict_types=1);
/**
 * Scan additional image names from filesystem to database table
 *
 * Copyright 2003-2025 Zen Cart Development Team
 * copyright ZenExpert 2025
 */
require 'includes/application_top.php';

$products_query = $db->Execute(
    "SELECT count(products_id) AS total
        FROM " . TABLE_PRODUCTS . "
        WHERE products_image IS NOT NULL AND products_image != ''
        AND products_image != '" . zen_db_input(PRODUCTS_IMAGE_NO_IMAGE) . "'"
);
$totalProducts = ($products_query->EOF) ? 0 : (int)$products_query->fields['total'];

?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>

    <style>
        body { background: #f8f9fa; }
        header { margin: 24px 0 12px; }
        .section-spacing { margin-top: 14px; }
        .controls-row { margin-bottom: 12px; }
        #log {
            background: #0b0d12; color: #cbd5e1; padding: 12px; border-radius: 8px;
            height: 260px; overflow: auto; white-space: pre-wrap; margin-top: 8px;
        }
        .sr-only { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0; }
        .collapse-toggle { margin-bottom: 8px; }

        .progress { height: 22px; }
        .progress-label {display: inline-block;margin-left: 8px;font-weight: 600;min-width: 3ch;}

        .stats .stat-box {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
            padding: 12px; margin-bottom: 12px;
        }
        .stats .label { font-size: 0.9rem; color: #6c757d; }
        .stats .value { font-size: 1.25rem; font-weight: 600; margin: 5px 5px 0; }
    </style>

    <script title="Total Products With Images">
        const totalProducts = <?= (int)$totalProducts ?>;
    </script>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->

<div class="container">
    <!-- body_text //-->

    <h1><?= HEADING_TITLE ?></h1>
    <?= TEXT_MAIN ?><br>
    <br>
    <h3><?= TEXT_STEP_1 ?></h3>
    <p><?= TEXT_STEP_1_DETAIL ?></p>
    <h3><?= TEXT_STEP_2 ?></h3>
    <p><?= TEXT_STEP_2_DETAIL ?></p>
    <h3><?= TEXT_STEP_3 ?></h3>
    <p><?= TEXT_STEP_3_DETAIL ?></p>

    <hr/>

    <!-- Controls Row -->
    <div class="controls-row">
        <div class="btn-toolbar" role="toolbar" aria-label="Batch controls">
            <div class="btn-group me-2" role="group" aria-label="Run">
                <button id="btnStart" type="button" class="btn btn-primary"><?= BUTTON_START_SCANNING ?></button>
                <button id="btnCancel" type="button" class="btn btn-warning" disabled><?= TEXT_CANCEL ?></button>
            </div>

            <!-- Advanced toggler (supports BS3/4 and BS5 attributes) -->
            <button
                id="btnAdvancedToggle"
                class="btn btn-outline-secondary collapse-toggle"
                type="button"
                data-toggle="collapse" data-target="#advancedCollapse"
                data-bs-toggle="collapse" data-bs-target="#advancedCollapse"
                aria-expanded="false" aria-controls="advancedCollapse">
                <?= TEXT_SETTINGS ?> <span class="sr-only"><?= TEXT_TOGGLE_SECTION ?></span>
            </button>
        </div>
    </div>

    <!-- Advanced (Collapse) -->
    <div class="collapse" id="advancedCollapse">
        <div class="card card-body" style="border:1px solid #e5e7eb;">
            <form class="form">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="start_at" class="control-label"><?= TEXT_START_AT ?></label>
                            <input id="start_at" type="number" min="0" value="0" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label for="batch_size" class="control-label"><?= TEXT_BATCH_SIZE ?></label>
                            <input id="batch_size" type="number" min="0" max="100" value="10" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="text-muted"><?= TEXT_SETTINGS_HELP ?></div>
            </form>
        </div>
    </div>

    <!-- Progress -->
    <section aria-labelledby="progressHeading" class="section-spacing">
        <h2 id="progressHeading" class="h5"><?= TEXT_PROGRESS ?></h2>

        <div class="row">
            <div class="col-xs-12 col-md-10">
                <div class="progress" aria-describedby="progressLabel">
                    <div id="progressBar" class="progress-bar progress-bar-success bg-success" role="progressbar"
                         aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                         style="width: 0%;"></div>
                </div>
            </div>
            <div class="col-xs-12 col-md-2">
                <span id="progressLabel" class="progress-label">0%</span>
            </div>
        </div>
    </section>


    <!-- Stats -->
    <section aria-labelledby="statsHeading" class="section-spacing">
        <h2 id="statsHeading" class="h5">Stats</h2>

        <div class="row stats">
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_TOTAL_PRODUCTS_WITH_IMAGES ?></div>
                    <div id="totalVal" class="value">—</div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_CUMULATIVE_PROCESSED ?></div>
                    <div id="statProcessed" class="value">0</div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_CUMULATIVE_INSERTED ?></div>
                    <div id="statImages" class="value">0</div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_THIS_BATCH_FOUND ?></div>
                    <div id="statBatchFound" class="value">0</div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_THIS_BATCH_INSERTED ?></div>
                    <div id="statBatchImages" class="value">0</div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="stat-box">
                    <div class="label"><?= TEXT_PRODUCTS_REMAINING ?></div>
                    <div id="statRemaining" class="value">0</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Message Log -->
    <section aria-labelledby="logHeading" class="section-spacing">
        <h2 id="logHeading" class="h5"><?= TEXT_MESSAGE_LOG ?></h2>
        <pre id="log" aria-live="polite"></pre>
    </section>
    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->

<script>
    (function ($) {
        'use strict';

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function appendLog(line) {
            let logEl = $('#log');
            let time = new Date().toLocaleTimeString();
            let safe = escapeHtml('[' + time + '] ' + line);
            logEl.append(safe + '\n');
            logEl.scrollTop(logEl[0].scrollHeight);
        }

        function setRunningState(isRunning) {
            $('#runState').text(isRunning ? '<?= TEXT_RUNNING ?>' : '<?= TEXT_IDLE ?>');
            $('#btnStart').prop('disabled', isRunning);
            $('#btnCancel').prop('disabled', !isRunning);

            // Disable Advanced inputs and toggler while running
            $('#start_at, #batch_size').prop('disabled', isRunning);
            $('#btnAdvancedToggle').prop('disabled', isRunning);
        }

        function clamp(val, min, max) {
            if (!Number.isFinite(val)) return min;
            return Math.max(min, Math.min(max, val));
        }

        function updateProgress(pct) {
            pct = clamp(pct, 0, 100);
            $('#progressBar')
                .css('width', pct + '%')
                .attr('aria-valuenow', pct);
            $('#progressLabel').text(pct + '%');
        }

        function updateStats(stats) {
            if (stats.totalVal != null) $('#totalVal').text(stats.totalVal);
            if (stats.processed != null) $('#statProcessed').text(stats.processed);
            if (stats.images != null) $('#statImages').text(stats.images);
            if (stats.batchFound != null) $('#statBatchFound').text(stats.batchFound);
            if (stats.batchImages != null) $('#statBatchImages').text(stats.batchImages);
            if (stats.remaining != null) $('#statRemaining').text(stats.remaining);
        }

        let cancelRequested = false;

        $(function () {
            $('#totalVal').text(totalProducts | '-');

            $('#btnStart').on('click', onStart);
            $('#btnCancel').on('click', function () {
                cancelRequested = true;
            });
        });

        async function onStart() {
            let start_at = parseInt($('#start_at').val(), 10);
            let batch_size = parseInt($('#batch_size').val(), 10);

            start_at = clamp(Number.isFinite(start_at) ? start_at : 0, 0, Number.POSITIVE_INFINITY);
            batch_size = clamp(Number.isFinite(batch_size) ? batch_size : 10, 0, 100);

            // Reset state/UI
            cancelRequested = false;
            setRunningState(true);

            let cumulativeProcessed = 0;
            let cumulativeImages = 0;
            let batchFound = 0;
            let batchImages = 0;
            let remaining = 0;
            let initialRemaining = null;

            updateStats({
                processed: cumulativeProcessed,
                images: cumulativeImages,
                batchFound: 0,
                batchImages: 0,
                remaining: 0
            });
            updateProgress(0);

            appendLog('<?= TEXT_STARTED_WITH ?>' + start_at + ', <?= TEXT_WITH_BATCH_SIZE ?>' + batch_size + '.');

            while (!cancelRequested) {
                try {
                    let payload = { start_at: start_at, batch_size: batch_size };

                    let response = await zcJS.ajax({
                        url: '/ajax.php?act=ajaxScanAdditionalImages&method=doBatch',
                        data: payload,
                        timeout: 60000,
                        cache: false,
                        headers: { "cache-control": "no-cache" },
                    });

                    if (!response || typeof response !== 'object') {
                        appendLog('<?= ERROR_EMPTY_RESPONSE ?>');
                        break;
                    }

                    let batchRecordsFound = Number.isFinite(+response.batchRecordsFound) ? +response.batchRecordsFound : 0;
                    let recordsProcessed = Number.isFinite(+response.recordsProcessed) ? +response.recordsProcessed : 0;
                    let next_start = response.next_start;
                    let next_batch = response.next_batch;
                    let terminate = !!response.terminate;
                    let imagesInserted = Number.isFinite(+response.imagesInserted) ? +response.imagesInserted : 0;
                    let errorMessage = response.errorMessage;
                    remaining = +response.remaining;

                    if (terminate === true) {
                        if (errorMessage) {
                            appendLog('<?= TEXT_ERROR ?>' + escapeHtml(String(errorMessage)));
                        } else {
                            appendLog('<?= TEXT_SERVER_ENDED ?>');
                        }
                        break;
                    }

                    if (!Number.isFinite(remaining)) {
                        appendLog('<?= TEXT_MISSING_RESPONSE ?>');
                        break;
                    }

                    // On first loop, capture initial remaining count
                    if (initialRemaining == null) {
                        initialRemaining = (remaining > 0) ? remaining : 1;
                    }

                    cumulativeProcessed += recordsProcessed;
                    cumulativeImages += imagesInserted;
                    batchFound = batchRecordsFound;
                    batchImages = imagesInserted;

                    updateStats({
                        processed: cumulativeProcessed,
                        images: cumulativeImages,
                        batchFound: batchFound,
                        batchImages: batchImages,
                        remaining: remaining
                    });

                    // Progress meter
                    let pct = Math.round(((initialRemaining - remaining) / initialRemaining) * 100);
                    pct = clamp(pct, 0, 100);
                    updateProgress(pct);

                    // Display log
                    appendLog(
                        '<?= TEXT_STATUS_FOUND ?> ' + batchRecordsFound +
                        ', <?= TEXT_STATUS_PRODUCTS ?> ' + recordsProcessed +
                        ', <?= TEXT_STATUS_IMAGES ?> ' + imagesInserted +
                        ', <?= TEXT_STATUS_REMAINING ?>' + remaining
                    );

                    // Log any server warnings
                    if (errorMessage) {
                        appendLog('<?= TEXT_WARNING ?>' + escapeHtml(String(errorMessage)));
                    }

                    // When done
                    if (remaining === 0) {
                        updateProgress(100);
                        appendLog('<?= TEXT_COMPLETED ?>');
                        break;
                    }

                    // Loop using supplied server cursors for successive iterations
                    if (Number.isFinite(+next_start)) start_at = +next_start;
                    if (Number.isFinite(+next_batch)) batch_size = +next_batch;

                } catch (jqXHR) {
                    // Non-2xx or transport error
                    let status = (jqXHR && typeof jqXHR.status === 'number') ? jqXHR.status : 0;
                    if (status === 0) {
                        appendLog('<?= TEXT_NETWORK_ERROR ?>');
                    } else {
                        appendLog('<?= TEXT_HTTP_ERROR ?>' + status + '.');
                    }
                    break;
                }
            }

            // Post-loop
            if (cancelRequested) {
                appendLog('<?= TEXT_CANCELLED ?>');
            }

            setRunningState(false);
        }

    })(jQuery);
</script>

</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
