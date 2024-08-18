<?php

/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2022 Aug 03 Modified in v1.5.8-alpha2 $
 */
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php';
?>
<form id="system_setup" name="system_setup" method="post" action="index.php?main_page=database" class="needs-validation">
    <input type="hidden" name="action" value="process">
    <input type="hidden" name="lng" value="<?= $installer_lng ?>">
    <input type="hidden" name="dir_ws_http_catalog" value="<?= $dir_ws_http_catalog ?>">
    <input type="hidden" name="dir_ws_https_catalog" value="<?= $dir_ws_https_catalog ?>">
    <input type="hidden" name="detected_detected_http_server_catalog" value="<?= $catalogHttpServer ?>">
    <input type="hidden" name="detected_detected_https_server_catalog" value="<?= $catalogHttpsServer ?>">
    <input type="hidden" name="adminDir" value="<?= $adminDir ?>">
    <input type="hidden" name="db_type" value="<?= $db_type ?>">

    <fieldset class="border rounded p-3 mt-2">
        <legend>License</legend>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="agreeLicense">
                    <a href="#" class="hasHelpText icon-link" id="AGREETOTERMS">
                        <?= TEXT_SYSTEM_SETUP_AGREE_LICENSE ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-8 col-lg-9">
                <input class="form-check-input is-invalid" type="checkbox" name="agreeLicense" id="agreeLicense" tabindex="1" required value="agree">
                <label class="form-check-label" for="agreeLicense"><?= TEXT_SYSTEM_SETUP_CLICK_TO_AGREE_LICENSE ?></label>
                <div class="invalid-feedback"><?= TEXT_FORM_VALIDATION_AGREE_LICENSE ?></div>
            </div>
        </div>
    </fieldset>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_SYSTEM_SETUP_ADMIN_SETTINGS ?></legend>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="http_server_admin">
                    <a href="#" class="hasHelpText icon-link" id="ADMINSERVERDOMAIN">
                        <?= TEXT_SYSTEM_SETUP_ADMIN_SERVER_DOMAIN ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-7">
                <input id="http_server_admin" class="form-control" type="url" value="<?= $adminServer ?>" name="http_server_admin" tabindex="2" placeholder="ie: https:/www.your_domain.com" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_ADMINSERVERDOMAIN ?></div>
            </div>
        </div>
    </fieldset>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_SYSTEM_SETUP_CATALOG_SETTINGS ?></legend>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="form-check-label" for="enable_ssl_catalog">
                    <a href="#" class="hasHelpText icon-link" id="ENABLESSLCATALOG">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-7">
                <input class="form-check-input" id="enable_ssl_catalog" type="checkbox" value="true" name="enable_ssl_catalog" tabindex="3" <?= $enableSslCatalog ?>>
                <label class="form-check-label" for="enable_ssl_catalog"><?= TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL ?></label>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="http_server_catalog">
                    <a href="#" class="hasHelpText icon-link" id="HTTPSERVERCATALOG">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_HTTP_SERVER_DOMAIN ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-7">
                <input id="http_server_catalog" class="form-control" type="url" value="<?= $catalogHttpServer ?>" name="http_server_catalog" tabindex="4" placeholder="ie: http:/www.your_domain.com" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_HTTPSERVERCATALOG ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="http_url_catalog">
                    <a href="#" class="hasHelpText icon-link" id="HTTPURLCATALOG">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_HTTP_URL ?>
                    <i class="bi-question-circle"></i></a>
                </label>
            </div>
            <div class="col-7">
                <input id="http_url_catalog" class="form-control" type="url" value="<?= $catalogHttpUrl ?>" name="http_url_catalog" tabindex="5" placeholder="ie: http:/www.your_domain.com">
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_HTTPURLCATALOG ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="https_server_catalog">
                    <a href="#" class="hasHelpText icon-link" id="HTTPSSERVERCATALOG">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_HTTPS_SERVER_DOMAIN ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-7">
                <input id="https_server_catalog" class="form-control" type="url" value="<?= $catalogHttpsServer ?>" name="https_server_catalog" tabindex="6" placeholder="ie: https:/www.your_domain.com" required>
                <div class="invalid-feedback"><?= TEXT_FORM_VALIDATION_CATALOG_HTTPS_URL ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="https_url_catalog">
                    <a href="#" class="hasHelpText icon-link" id="HTTPSURLCATALOG">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_HTTPS_URL ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-7">
                <input id="https_url_catalog" class="form-control" type="url" value="<?= $catalogHttpsUrl ?>" name="https_url_catalog" tabindex="7" placeholder="ie: https:/www.your_domain.com">
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_HTTPSURLCATALOG ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-xs-5 col-md-4 col-lg-3">
                <label class="col-form-label" for="physical_path">
                    <a href="#" class="hasHelpText icon-link" id="PHYSICALPATH">
                        <?= TEXT_SYSTEM_SETUP_CATALOG_PHYSICAL_PATH ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-12 col-md-7">
                <input id="physical_path" class="form-control" type="text" value="<?= $documentRoot ?>" name="physical_path" tabindex="8" placeholder="ie: /yourserver/users/yourname/public_html/zencart" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_PHYSICALPATH ?></div>
            </div>
        </div>
    </fieldset>
    <input type="submit" class="btn btn-primary mt-3" id="btnsubmit" name="btnsubmit" value="<?= TEXT_CONTINUE ?>" tabindex="9">
</form>

<?php require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_system_setup_errors.php'; ?>

<script>
    async function validateForm(form) {
        await fetch("ajaxTestSystemSetup.php", {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(new FormData(form)).toString()
        }).then((response) => response.json())
        .then((data) => {
            if (!data.error) {
                form.submit();
                return true;
            } else {
                let errorList = data.errorList;
                let errorString = '';
                let licenseError = false;
                for (let i in errorList) {
                    errorString += '<li>' + errorList[i] + '</li>';
                    document.getElementById(i).classList.add("is-invalid", "invalid", "form-control:invalid", "form-control:is-invalid");
                    document.getElementById(i).classList.remove("is-valid", "valid", "form-control:valid", "form-control:is-valid");
                    if (i === 'agreeLicense') {
                        licenseError = true;
                    }
                }
                (document.getElementById('system-setup-errors-content')).innerHTML= '<ul>'+errorString+'</ul>';

                if (licenseError === true) {
                    (document.getElementById('system-setup-errors-submit')).style.display = 'none';
                } else {
                    let modalButton = document.getElementById('system-setup-errors-submit');
                    modalButton.addEventListener('click', event => {
                        event.target.form.submit();
                    });
                    modalButton.style.display = '';
                }

                (new bootstrap.Modal('#system-setup-errors')).show();
                form.classList.remove("was-validated");
            }
        }).catch(error => {
        // Handle error
        });
    }


</script>
