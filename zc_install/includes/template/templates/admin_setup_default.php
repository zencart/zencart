<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_admin_validation_errors.php';
?>

<?php
if (!empty($errors)) { ?>
    <div class="alert alert-danger">
    <?php
    foreach ($errors as $errormessage) {
        echo $errormessage . '<br>';
    }
    echo TEXT_ERROR_PROBLEMS_WRITING_CONFIGUREPHP_FILES;
    ?>
    </div>
<?php
}
?>

<form id="admin_setup" name="admin_setup" method="post" action="index.php?main_page=completion" class="needs-validation">
    <input type="hidden" name="action" value="process">
    <input type="hidden" name="lng" value="<?= $installer_lng ?>">
    <?php
    foreach ($_POST as $key => $value) { ?>
    <?php
    if ($key !== 'action') { ?>
        <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
    <?php
    } ?>
    <?php
    } ?>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_ADMIN_SETUP_USER_SETTINGS ?></legend>
        <div class="row mb-2">
            <div class="col-5 col-sm-6 col-md-4 col-lg-3">
                <label class="col-form-label" for="admin_user">
                    <a href="#" class="hasHelpText icon-link" id="ADMINUSER">
                        <?= TEXT_ADMIN_SETUP_USER_NAME ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-6 col-md-5 col-lg-4">
                <input class="form-control" type="text" name="admin_user" id="admin_user" value="" tabindex="1" autofocus="autofocus" placeholder="<?= TEXT_EXAMPLE_USERNAME ?>" required autocomplete="off">
                <div class="invalid-feedback">A unique admin username is required</div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-5 col-sm-6 col-md-4 col-lg-3">
                <label class="col-form-label" for="admin_email">
                    <a href="#" class="hasHelpText icon-link" id="ADMINEMAIL">
                        <?= TEXT_ADMIN_SETUP_USER_EMAIL ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-6 col-md-5 col-lg-4">
                <input class="form-control" type="email" name="admin_email" id="admin_email" value="" tabindex="2" placeholder="<?= TEXT_EXAMPLE_EMAIL ?>" required autocomplete="off">
                <div class="invalid-feedback">A valid email address is required</div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-5 col-sm-6 col-md-4 col-lg-3">
                <label class="col-form-label" for="admin_email2">
                    <a href="#" class="hasHelpText icon-link" id="ADMINEMAIL2">
                        <?= TEXT_ADMIN_SETUP_USER_EMAIL_REPEAT ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-6 col-md-5 col-lg-4">
                <input class="form-control" type="email" name="admin_email2" id="admin_email2" value="" tabindex="3" placeholder="<?= TEXT_EXAMPLE_EMAIL ?>" required data-equalto="admin_email" autocomplete="off">
                <div class="invalid-feedback"><? TEXT_ADMIN_SETUP_MATCHING_EMAIL ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col alert alert-danger"><?= TEXT_ADMIN_SETUP_USER_PASSWORD_HELP ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-5 col-sm-6 col-md-4 col-lg-3">
                <label class="col-form-label" for="admin_password">
                    <a href="#" class="hasHelpText icon-link" id="ADMINPASSWORD">
                        <?= TEXT_ADMIN_SETUP_USER_PASSWORD ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-6 col-md-5 col-lg-4">
                <input class="form-control" type="text" name="admin_password" id="admin_password" value="<?= $admin_password ?>" readonly="readonly" tabindex="4" autocomplete="off">
            </div>
        </div>
        <div class="row mb-2">
            <?php
            if ($changedDir) { ?>
                <div class="col alert alert-primary"><?= TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_CHANGED ?></div>
            <?php
            } elseif (!$changedDir && $adminNewDir === 'admin') { ?>
                <div class="col alert alert-danger"><?= TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_DEFAULT ?></div>
            <?php
            } else { ?>
                <div class="col alert alert-primary"><?= TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_NOT_ADMIN_CHANGED ?></div>
            <?php
            } ?>
        </div>
        <div class="row mb-2">
            <div class="col-5 col-sm-6 col-md-4 col-lg-3">
                <label class="col-form-label" for="admin_directory">
                    <a href="#" class="hasHelpText icon-link" id="ADMINDIRECTORY">
                        <?= TEXT_ADMIN_SETUP_ADMIN_DIRECTORY ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-6 col-md-5 col-lg-4">
                <input class="form-control" type="text" name="admin_directory" id="admin_directory" value="<?= $adminNewDir ?>" readonly="readonly" tabindex="5" autocomplete="off">
            </div>
        </div>
    </fieldset>
    <input class="btn btn-primary mt-3" type="submit" id="btnsubmit" name="btnsubmit" value="<?= TEXT_CONTINUE ?>" tabindex="10">
</form>

<script>
    (() => {
        'use strict'
        document.getElementById('admin_email2').addEventListener('change', el => {
            if (el.target.value === $('#admin_email').value) {
                el.target.classList.remove("form-control:is-invalid", "form-control:invalid", "is-invalid", "invalid");
                el.target.classList.add("form-control:valid", "form-control:is-valid", "is-valid", "valid");
            } else {
                el.target.classList.add("form-control:is-invalid", "form-control:invalid", "is-invalid", "invalid");
                el.target.classList.remove("form-control:valid", "form-control:is-valid", "is-valid", "valid");
            }
        })
    })();

    async function validateForm(form) {
        let str = $(form).serialize();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: str,
            url: 'ajaxAdminSetup.php',
            success: function (data) {
                if (!data.error) {
                    form.submit();
                    return true;
                } else {
                    let errorList = data.errorList;
                    let errorString = '';
                    for (let i in errorList) {
                        errorString += '<li>' + errorList[i] + '</li>';
                        document.getElementById(i).classList.add("form-control:is-invalid", "form-control:invalid", "is-invalid", "invalid");
                        document.getElementById(i).classList.remove("form-control:valid", "form-control:is-valid", "is-valid", "valid");
                    }
                    $("#admin-validation-errors-content").html('<ul>'+errorString+'</ul>');

                    (new bootstrap.Modal('#admin-validation-errors')).show();
                    form.classList.remove("was-validated");
                }
                return false;
            }
        });
    }
</script>
