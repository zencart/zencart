<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */


require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_progress_bar.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_connection_errors.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_install_errors.php';
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php';
?>

<form id="db_setup" name="db_setup" method="post" action="index.php?main_page=admin_setup" class="needs-validation">
    <input type="hidden" name="action" value="process">
    <input type="hidden" name="lng" value="<?= zc_install_escape_html($installer_lng) ?>">
    <?= zc_install_render_hidden_post_fields($_POST, ['action', 'lng']) ?>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_DATABASE_SETUP_SETTINGS ?></legend>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="db_host">
                    <a href="#" class="hasHelpText icon-link" id="DBHOST">
                        <?= TEXT_DATABASE_SETUP_DB_HOST ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <input class="form-control" type="text" name="db_host" id="db_host" value="<?= zc_install_escape_html($db_host) ?>" tabindex="1" autofocus="autofocus" placeholder="<?= zc_install_escape_html(TEXT_EXAMPLE_DB_HOST) ?>" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_DBHOST ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="db_user">
                    <a href="#" class="hasHelpText icon-link" id="DBUSER">
                        <?= TEXT_DATABASE_SETUP_DB_USER ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <input class="form-control" type="text" name="db_user" id="db_user" value="<?= zc_install_escape_html($db_user) ?>" tabindex="2" placeholder="<?= zc_install_escape_html(TEXT_EXAMPLE_DB_USER) ?>" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_DBUSER ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="db_password">
                    <a href="#" class="hasHelpText icon-link" id="DBPASSWORD">
                        <?= TEXT_DATABASE_SETUP_DB_PASSWORD ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <input class="form-control" type="password" name="db_password" id="db_password" value="<?= zc_install_escape_html($db_password) ?>" tabindex="3" placeholder="<?= zc_install_escape_html(TEXT_EXAMPLE_DB_PWD) ?>">
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_DBPASSWORD ?></div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="db_name">
                    <a href="#" class="hasHelpText icon-link" id="DBNAME">
                        <?= TEXT_DATABASE_SETUP_DB_NAME ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <input class="form-control" type="text" name="db_name" id="db_name" value="<?= zc_install_escape_html($db_name) ?>" tabindex="4" placeholder="<?= zc_install_escape_html(TEXT_EXAMPLE_DB_NAME) ?>" required>
                <div class="invalid-feedback"><?= TEXT_HELP_CONTENT_DBNAME ?></div>
            </div>
        </div>
    </fieldset>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_DATABASE_SETUP_DEMO_SETTINGS ?></legend>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="demoData">
                    <a href="#" class="hasHelpText icon-link" id="DEMODATA">
                        <?= TEXT_DATABASE_SETUP_LOAD_DEMO ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="demoData" id="demoData" tabindex="5" <?= $install_demo_data ? 'checked' : '' ?>>
                    <label class="form-check-label" for="demoData"><?= TEXT_DATABASE_SETUP_LOAD_DEMO_DESCRIPTION ?></label>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset class="border rounded p-3 mt-2">
        <legend><?= TEXT_DATABASE_SETUP_ADVANCED_SETTINGS ?></legend>
        <input type="hidden" name="db_charset" value="utf8mb4">
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="db_prefix">
                    <a href="#" class="hasHelpText icon-link" id="DBPREFIX">
                        <?= TEXT_DATABASE_SETUP_DB_PREFIX ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <input class="form-control" type="text" name="db_prefix" id="db_prefix" value="<?= zc_install_escape_html($db_prefix) ?>" tabindex="7" placeholder="<?= zc_install_escape_html(TEXT_EXAMPLE_DB_PREFIX) ?>">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-sm-5 col-lg-3">
                <label class="col-form-label" for="sql_cache_method">
                    <a href="#" class="hasHelpText icon-link" id="SQLCACHEMETHOD">
                        <?= TEXT_DATABASE_SETUP_SQL_CACHE_METHOD ?>
                        <i class="bi-question-circle"></i>
                    </a>
                </label>
            </div>
            <div class="col-xs-7 col-md-6">
                <select class="form-select" name="sql_cache_method" id="sql_cache_method" tabindex="8"><?= $sqlCacheTypeOptions ?></select>
            </div>
        </div>
    </fieldset>
    <input type="submit" class="btn btn-primary mt-3" id="btnsubmit" name="btnsubmit" value="<?= TEXT_CONTINUE ?>" tabindex="10">
</form>

<script>
    async function validateForm(form) {
        let str = new URLSearchParams(new FormData(form)).toString()
        let myform = form;
        await fetch("ajaxTestDBConnection.php", {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: str
        }).then((response) => response.json())
        .then((data) => {
            if (data.error === true) {
                const container = document.getElementById("connection-errors-content");
                container.replaceChildren();
                const heading = document.createElement('h4');
                heading.textContent = data.errorList['mainErrorText'];
                container.appendChild(heading);
                const errorList = document.createElement('ul');
                for (let i in data.errorList['extraErrors']) {
                    const item = document.createElement('li');
                    item.textContent = data.errorList['extraErrors'][i];
                    errorList.appendChild(item);
                }
                container.appendChild(errorList);
                (new bootstrap.Modal('#connection-errors')).show();
                form.classList.remove("was-validated");
            } else {
                (new bootstrap.Modal('#progress-bar-dialog')).show();
                setTimeout("updateStatus()", 10);
                fetch("ajaxLoadMainSql.php", {
                    method: 'post',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: str
                }).then((response) => response.json())
                .then((data) => {
                    if (data.error === true) {
                        const container = document.getElementById('install-errors-content');
                        container.replaceChildren();
                        const paragraph = document.createElement('p');
                        paragraph.appendChild(document.createTextNode(<?= json_encode(TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS1) ?> + data.file));
                        paragraph.appendChild(document.createElement('br'));
                        paragraph.appendChild(document.createTextNode(<?= json_encode(trim(strip_tags(TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS2))) ?>));
                        container.appendChild(paragraph);
                        (new bootstrap.Modal('#install-errors')).show();
                    } else {
                        fetch("ajaxAdminSetup.php", {
                            method: 'post',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
	                            body: str
	                        }).then((response) => response.json())
	                        .then((data) => {
                                if (data.error === true) {
                                    const errorList = document.createElement('ul');
                                    for (let i in data.errorList) {
                                        const item = document.createElement('li');
                                        item.textContent = data.errorList[i];
                                        errorList.appendChild(item);
                                    }
                                    const container = document.getElementById('install-errors-content');
                                    container.replaceChildren(errorList);
                                    (new bootstrap.Modal('#install-errors')).show();
                                    return;
                                }
	                            const i1 = document.createElement("input");
	                            i1.type = 'hidden';
                            i1.name = 'adminDir';
                            i1.value = data.adminDir;
                            myform.appendChild(i1);
                            const i2 = document.createElement("input");
                            i2.type = 'hidden';
                            i2.name = 'changedDir';
                            i2.value = data.changedDir;
                            myform.appendChild(i2);
                            const i3 = document.createElement("input");
                            i3.type = 'hidden';
                            i3.name = 'adminNewDir';
                            i3.value = data.adminNewDir;
                            myform.appendChild(i3);
                            myform.submit();
                        }).catch(error => {
                            //
                        });
                    }
                });
            }
        });
    }
</script>
