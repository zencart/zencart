<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */

require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php'; ?>

<div class="alert alert-success text-center">
    <div class="showModal btn btn-warning rounded text-center py-3 pt-4 mb-3" id="NGINXCONF">
        <h6><?= TEXT_COMPLETION_NGINX_TEXT ?> <i class="bi-question-circle"></i></h6>
    </div>

    <?php
    if ($adminDir === 'admin' && !defined('DEVELOPER_MODE')) { ?>
        <div class="alert alert-secondary py-3">
            <h6><?= TEXT_COMPLETION_ADMIN_DIRECTORY_WARNING ?></h6>
        </div>
    <?php
    }
    ?>

    <?php
    if (file_exists(DIR_FS_INSTALL) && !defined('DEVELOPER_MODE')) { ?>
        <div class="alert alert-secondary py-3">
            <h6><?= TEXT_COMPLETION_INSTALLATION_DIRECTORY_WARNING ?></h6>
            <h6><?= TEXT_COMPLETION_INSTALLATION_DIRECTORY_EXPLANATION ?></h6>
        </div>
    <?php
    } ?>

    <div class="alert alert-primary py-3">
        <h5>
            <?php
            if ($isUpgrade) { ?>
                <?= TEXT_COMPLETION_UPGRADE_COMPLETE ?>
            <?php
            } else { ?>
                <?= TEXT_COMPLETION_INSTALL_COMPLETE ?>

                <?php
                if ($catalogLink !== '#') {
                    echo TEXT_COMPLETION_INSTALL_LINKS_BELOW;
                } ?>
            <?php
            } ?>
        </h5>

        <?php
        if (!$isUpgrade && $catalogLink !== '#') { ?>

            <div class="text-center">
                <a class="btn btn-primary rounded p-4" href="<?= $adminLink ?>" rel="noopener" target="_blank" tabindex="1">
                    <?= TEXT_COMPLETION_ADMIN_LINK_TEXT ?>:
                    <br>
                    <u><?= $adminLink ?></u>
                </a>
                <a class="btn btn-primary rounded p-4" href="<?= $catalogLink ?>" rel="noopener" target="_blank" tabindex="2">
                    <?= TEXT_COMPLETION_CATALOG_LINK_TEXT ?>:
                    <br>
                    <u><?= $catalogLink ?></u>
                </a>
            </div>
        <?php
        } ?>
    </div>

</div>
<script>
    $(function () {
        $('.showModal').click(function (e) {
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
            e.preventDefault();
        })
    });
</script>
