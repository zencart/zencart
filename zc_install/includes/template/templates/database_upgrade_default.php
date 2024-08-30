<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2024 Jan 20 Modified in v2.0.0-alpha1 $
 */

require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_progress_bar.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_admin_validation_errors.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php';

if (count($newArray)) { ?>
    <div class="upgrade-progress-area">
        <div class="alert alert-primary" id="upgradeHeaderMessage"><?= TEXT_DATABASE_UPGRADE_STEPS_DETECTED ?></div>
    </div>
<?php
} ?>

<div id="upgradeResponsesHolder"></div>

<form id="db_upgrade<?= (count($newArray)) ? '' : '_done' ?>" name="db_upgrade" method="post" action="index.php?main_page=completion" class="needs-validation">
    <input type="hidden" name="lng" value="<?= $installer_lng ?>">
    <input type="hidden" name="action" value="process">
    <?php
    if (count($newArray)) { ?>
        <input type="hidden" name="upgrade_mode" value="yes">
        <fieldset id="availableUpgradeSteps" class="border rounded p-3 bg-info-subtle">
            <legend><?= TEXT_DATABASE_UPGRADE_LEGEND_UPGRADE_STEPS ?></legend>
            <div class="row">
                <div class="col">
                    <?php
                    foreach ($newArray as $key => $value) { ?>
                        <?php
                        $from = ($key === 0) ? ($dbVersion ?? $versionArray[$value]['required']): $newArray[($key - 1)]; ?>
                        <?php
                        $to = $value; ?>
                        <div id="label-version-<?= str_replace('.', '_', $value) ?>" class="checkbox-wrapper">
                            <label class="form-check-label" for="version-<?= str_replace('.', '_', $value) ?>">
                                <input class="form-check-input" type="checkbox" name="version-<?= str_replace('.', '_', $value) ?>" id="version-<?= str_replace('.', '_', $value) ?>" checked="CHECKED">
                                <?= $from . ' to  ' . $to ?></label>
                        </div>
                    <?php
                    } ?>
                </div>
            </div>
        </fieldset>
        <fieldset class="upgrade-hide-area border rounded p-3 mt-2 bg-warning-subtle">
            <legend><?= TEXT_DATABASE_UPGRADE_ADMIN_CREDENTIALS ?></legend>
            <div class="row mt-2">
                <div class="col-3">
                    <label class="col-form-label" for="admin_user">
                        <a href="#" class="hasHelpText" id="UPGRADEADMINNAME">
                            <?= TEXT_DATABASE_UPGRADE_ADMIN_USER ?>
                            <i class="bi-question-circle"></i>
                        </a>
                    </label>

                </div>
                <div class="col-7 col-md-5 col-lg-3">
                    <input class="form-control" type="text" name="admin_user" id="admin_user" value="" tabindex="1" autofocus="autofocus" required autocomplete="username" >
                    <div class="invalid-feedback"><?= TEXT_VALIDATION_ADMIN_CREDENTIALS ?></div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <label class="col-form-label" for="admin_password">
                        <a href="#" class="hasHelpText" id="UPGRADEADMINPWD">
                            <?= TEXT_DATABASE_UPGRADE_ADMIN_PASSWORD ?>
                            <i class="bi-question-circle"></i>
                        </a>
                    </label>
                </div>
                <div class="col-7 col-md-5 col-lg-3">
                    <input class="form-control" type="password" name="admin_password" id="admin_password" value="" tabindex="2" required autocomplete="current-password">
                    <div class="invalid-feedback"><?= TEXT_VALIDATION_ADMIN_PASSWORD ?></div>
                </div>
            </div>
        </fieldset>
    <?php
    } elseif (empty($dbVersion)) { ?>
        <div>
            <div class="alert alert-danger round"><?= TEXT_CANNOT_DETECT_VERSION ?></div>
        </div>
    <?php
    } else { ?>
        <div>
            <div class="alert alert-success round"><?= TEXT_NO_REMAINING_UPGRADE_STEPS ?></div>
        </div>
    <?php
    } ?>
    <div class="upgrade-continue-button">
        <input type="submit" class="btn btn-primary mt-1" id="btnsubmit" name="btnsubmit" value="<?= TEXT_CONTINUE ?>" tabindex="3">
    </div>
</form>

<script>
    async function validateForm(form) {
        $('#upgradeResponsesHolder').html('');
        if (form.id === 'db_upgrade_done') {
            form.submit();
        }
        $.ajax({
            type: "POST",
            timeout: 10000,
            dataType: "json",
            data: $(form).serialize(),
            url: 'ajaxValidateAdminCredentials.php',
            success: function (data) {
                if (data.error) {
                    $('#admin-validation-errors-content').html('<p><?= TEXT_ERROR_ADMIN_CREDENTIALS ?></p>');
                    (new bootstrap.Modal('#admin-validation-errors')).show();
                } else {
                    $('#admin_password').val('');
                    $('#upgradeHeaderMessage').addClass('alert-secondary').text('<?= TEXT_UPGRADE_IN_PROGRESS ?>');
                    $('.upgrade-hide-area').hide();
                    $('.upgrade-continue-button').hide();
                    (new bootstrap.Modal('#progress-bar-dialog')).show();
                    setTimeout("updateStatus()", 10);
                    doAjaxUpdateSql(form);
                }
            }
        });
    }

    function doAjaxUpdateSql(form) {
        let deferred = $.Deferred();
        let promise = deferred.promise();
        let length = $('input[type=checkbox]:checked').length;
        let instance = 0;
        let error = false;
        let errorList = null;
        let response = null;
        $('input[type=checkbox]:checked').each(function () {
            let version = $(this).attr('id');
            promise = promise.pipe(
                function (response, status, ajax) {
                    if (response && response.error) {
                        deferred.reject();
                        error = true;
                        return promise;
                    } else {
                        instance ++;
                        console.log('Requesting upgrade for version ' + version + '; Step ' + instance + ' of ' + length);
                        return doRequest(version, length, instance);
                    }
                },
                function (response, status, ajax) {
                }
            )
            .done(function (response, status, ajax) {
                console.log('response.error=' + response.error);
                if (response.error && response.error === true) {
                    error = true;
                    let errorList = response.errorList;
                    let errorString = '';
                    $('#upgradeResponsesHolder').append('<div class="alert alert-danger round">' + errorList.join('<br>') + '</div>')
                    $('.upgrade-hide-area').show();
                    $('.upgrade-continue-button').show();
                } else {
                    console.log(response.version + ' Done');
                    let id = response.version.replace('version-', '');
                    id = id.replace(/_/g, '.');
                    $('#label-' + version).remove();
                    let str = '<?= TEXT_UPGRADE_TO_VER_X_COMPLETED ?>';
                    $('#upgradeResponsesHolder').append('<div class="alert alert-success round">' + str.replace('%s', id) + '</div>');
                }
            });
        });
        deferred.resolve();
        promise.done(function (response) {
            $('.upgrade-progress-area').hide();
            let length = $('input[type=checkbox]:not(:checked)').length;

            //console.log('DB Upgrade progress. Remaining checkboxes: '+length);
            if (length === 0) {
                $('#availableUpgradeSteps').hide();
                $('.upgrade-continue-button').show();
                form.classList.remove('needs-validation');
                form.noValidate = true;
            }
            if (!error && length === 0) {
                // Done, so submit form to move to the next page
                form.submit();
            } else {
                $('.upgrade-hide-area').show();
                $('.upgrade-continue-button').show();
            }
        });

        function doRequest(version, size, instance) {
            return $.ajax({
                type: "post",
                url: "ajaxLoadUpdatesSql.php",
                data: {version: version, batchSize: size, batchInstance: instance},
                dataType: "JSON"
            });
        }
    }
</script>
