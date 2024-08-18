<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

$show_language_selector = (count($languagesInstalled) > 1);
if (defined('DEVELOPER_MODE') && DEVELOPER_MODE === true) {
    $show_language_selector = true;
}
$skip_header = false;

if (empty($hasFatalErrors) && empty($hasWarnErrors) && (!empty($hasUpdatedConfigFile) || !empty($hasSaneConfigFile) || !empty($configFilePresent))) {
    $skip_header = true;
}

?>
<body id="<?= $body_id ?>">
<div class="container">
    <header class="header">
        <div class="row">
            <div class="col hero-unit">
                <div class="logo"></div>
                <div class="version"><?= sprintf(ZC_VERSION_STRING, PROJECT_VERSION_NAME, PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR) ?></div>
            </div>
        </div>
    </header>
    <div class="row mt-n3">
        <div class="col">
            <div class="mainContent">

            <?php require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_breadcrumb.php'; ?>

                <?php
                if ($is_home_page && $show_language_selector) { ?>
                    <form name="language_select" id="language_select" method="GET">
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <select name="lng" id="lng" class="form-select" aria-describedby="choose_lang">
                                        <?= zen_get_install_languages_list($installer_lng) ?>
                                    </select>
                                    <label class="form-label" for="lng" id="choose_lang"><?= TEXT_INSTALLER_CHOOSE_LANGUAGE ?></label>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php
                }
                ?>

                <h1><?= constant('TEXT_PAGE_HEADING_' . strtoupper($_GET['main_page'])) ?></h1>

            <?php
            if (defined('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN'))) {
                $header_text = constant('TEXT_' . strtoupper($_GET['main_page'] . '_HEADER_MAIN'));
            } elseif (TEXT_HEADER_MAIN !== '') {
                $header_text = constant('TEXT_HEADER_MAIN');
            }

            if (!empty($header_text && empty($skip_header))) { ?>
                <div class="alert alert-primary"><?= $header_text ?></div>
            <?php } ?>

            <?php require $body_code; ?>

            </div>

            <footer class="footer mt-4">
                <hr>
                <p>Copyright &copy; 2003-<?= date('Y') ?> <a href="https://www.zen-cart.com" rel="noopener" target="_blank">Zen Cart&reg;</a></p>
            </footer>
        </div>
    </div>

    <script>
        $().ready(function () {
            $('#lng').change(function (e) {
                $('#language_select').submit();
            });


            $('.hasNoHelpText').click(function (e) {
                e.preventDefault();
            })

            $('.hasHelpText').click(function (e) {
                e.preventDefault();
                let textId = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    timeout: 10000,
                    dataType: "json",
                    data: 'id=' + textId + '&lng=<?= $installer_lng ?>',
                    url: 'ajaxGetHelpText.php',
                    success: function (data) {
                        $('#modal-help-title').html(data.title);
                        $('#modal-help-content').html(data.text);
                        (new bootstrap.Modal('#modal-help')).show();
                    }
                });
            })

            $("#agreeLicense").on('change', function (e) {
                if (e.target.checked) {
                    e.target.classList.remove("is-invalid", "invalid")
                    e.target.classList.add("is-valid", "valid")
                } else {
                    e.target.classList.add("is-invalid", "invalid")
                    e.target.classList.remove("is-valid", "valid")
                }
            });
        });

        (() => {
            'use strict'

            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    event.preventDefault();
                    // validate HTML5
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                        form.classList.remove("was-validated");
                    } else {
                        // HTML5 was validated, now do ajax validation
                        if (validateForm(form) === false) {
                            event.preventDefault()
                            event.stopPropagation()
                            form.classList.remove("was-validated");
                            return false;
                        }
                    }
                    form.classList.add("was-validated");
                }, false)
            });
        })();

        function updateStatus() {
            $.ajax({
                type: "GET",
                dataType: "json",
                cache: false,
                url: "ajaxGetProgressValues.php",
                success: function (data) {
                    if (data.progress) {
                        writeProgressInfo(data)

                        // if (data.progress >= 0 && data.progress < 99) {
                        setTimeout("updateStatus()", 200);
                        // }
                    } else {
                        setTimeout("updateStatus()", 10);
                    }
                },
                error: function (data) {
                    setTimeout("updateStatus()", 10);
                }
            });
        }

        function writeProgressInfo(data) {
            $('#progress-meter-text').width(data.progress.toFixed(0) + '%');

            let progressMessage = '';
            if (data.message) {
                progressMessage = ' ' + data.message;
            }
            if (data.progress) {
                progressMessage = progressMessage + ' ' + data.progress.toFixed(0) + '%';
            }
            $('#progress-bar-title').html(progressMessage);
            $('#progress-meter-text').text(progressMessage);

            $('#progress-info').text(data.progressFeedback ?? '');
            $('#progress-container').scrollTop($('#progress-info').height());
        }
    </script>

</body>
</html>
