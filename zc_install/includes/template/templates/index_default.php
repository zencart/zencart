<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Aug 17 Modified in v2.1.0-alpha2 $
 */
require DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'partials/partial_modal_help.php';
$adjustWarnIssues = false;
?>
<form id="systemCheck" name="systemCheck" method="post" action="index.php?main_page=<?= $formAction ?>">
    <input type="hidden" name="lng" value="<?= $installer_lng ?>">
    <?php
    if ($hasMultipleAdmins) {
        $adjustWarnIssues = true;
        ?>
        <div class="alert alert-danger">
            <?php
            $alert_message = $selectedAdminDir !== '' ? TEXT_ERROR_MULTIPLE_ADMINS_SELECTED : TEXT_ERROR_MULTIPLE_ADMINS_NONE_SELECTED;
            ?>
            <?= $alert_message ?>

            <div class="row">
                <div class="col">

                    <div class="form-floating">
                        <select name="adminDir" id="adminDirSelect" class="form-select" aria-describedby="">
                            <?= zen_get_select_options($adminOptionList, $selectedAdminDir) ?>
                        </select>
                        <label class="form-label" for="adminDirSelect" id="choose_lang"><?= $alert_message ?></label>
                    </div>
                </div>
            </div>
        </div>

    <?php
    } else { ?>
        <input type="hidden" name="adminDir" value="<?= $selectedAdminDir ?>">
    <?php
    }
    ?>
    <?php
    if ($selectedAdminDir !== '') { ?>
    <?php

    if ($hasSaneConfigFile && !$otherConfigErrors && $hasUpdatedConfigFile) {
        if (!$isCurrentDb) {
            $adjustWarnIssues = true;
        ?>
        <div class="alert alert-success">
            <?= TEXT_ERROR_SUCCESS_EXISTING_CONFIGURE ?>
        </div>
        <?php
        } else { ?>
        <div class="alert alert-warning">
            <?= TEXT_ERROR_SUCCESS_EXISTING_CONFIGURE_NO_UPDATE ?>
        </div>
        <?php
        }
    }
    ?>
    <?php
    if (!$hasUpdatedConfigFile && $hasSaneConfigFile) { ?>
        <?php
        $adjustWarnIssues = true;
        ?>
        <div class="alert alert-danger">
            <?= TEXT_ERROR_CONFIGURE_REQUIRES_UPDATE ?>
        </div>

    <?php
    } ?>
    <?php
    if ($hasFatalErrors) {
        $adjustWarnIssues = true;
        ?>
        <div id="fatalErrors" class="errorList">
            <h3><?= TEXT_INDEX_FATAL_ERRORS ?></h3>
            <?php
            foreach ($listFatalErrors as $error) { ?>
                <div class="alert bg-danger">
                    <a href="" <?= (isset($error['mainErrorTextHelpId'])) ? 'class="hasHelpText" id="' . $error['mainErrorTextHelpId'] . '"' : 'class="hasNoHelpText link-light text-decoration-none"' ?>>
                        <?= ($error['mainErrorText']) ?>
                    </a>
                    <?php
                    if (isset($error['extraErrors'])) {
                        foreach ($error['extraErrors'] as $detailError) { ?>
                            <br><?= $detailError ?>
                    <?php
                        }
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    }

    if ($hasLocalAlerts) { ?>
        <?php
        $adjustWarnIssues = true;
        ?>
        <div id="alerts" class="errorList">
            <h3><?= TEXT_INDEX_ALERTS ?></h3>
            <?php
            foreach ($listLocalAlerts as $error) { ?>
                <div class="alert alert-warning">
                    <a href="" <?= (isset($error['mainErrorTextHelpId'])) ? 'class="hasHelpText" id="' . $error['mainErrorTextHelpId'] . '"' : 'class="hasNoHelpText link-dark text-decoration-none"' ?>>
                        <?= ($error['mainErrorText']) ?>
                        <?= (isset($error['mainErrorTextHelpId'])) ? '<i class="bi-question-circle"></i>' : '' ?>
                    </a>
                    <?php
                    if (isset($error['extraErrors'])) {
                        foreach ($error['extraErrors'] as $detailError) { ?>
                            <br><?= $detailError ?>
                        <?php
                        }
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    }

    if ($hasWarnErrors) {
    if (empty($errorHeadingFlag)) {
        $errorHeadingFlag = false;
    }

    foreach ($listWarnErrors as $error) {
        if (!str_contains($error['mainErrorText'], 'PRO TIP:')) {
            $errorHeadingFlag = true;
            break;
        }
    }
    ?>

    <div id="warnErrors" class="errorList">
        <?php
        if ($errorHeadingFlag) { ?>
            <h3><?= $adjustWarnIssues ? TEXT_INDEX_WARN_ERRORS : TEXT_INDEX_WARN_ERRORS_ALT ?></h3>
        <?php
        }
        foreach ($listWarnErrors as $error) {

    if (str_contains($error['mainErrorText'], 'PRO TIP:')) { ?>
        <div class="alert alert-danger">
            <?= ($error['mainErrorText']) ?>
            <?php
            } else {
        ?>
            <div class="alert alert-secondary">
                <a href="" <?= (isset($error['mainErrorTextHelpId'])) ? 'class="hasHelpText" id="' . $error['mainErrorTextHelpId'] . '"' : 'class="hasNoHelpText link-dark text-decoration-none"' ?>>
                    <?= ($error['mainErrorText']) ?>
                    <?= (isset($error['mainErrorTextHelpId'])) ? '<i class="bi-question-circle"></i>' : '' ?>
                </a>
                <?php
                } ?>
                <?php
                if (isset($error['extraErrors'])) { ?>
                    <?php
                    foreach ($error['extraErrors'] as $detailError) { ?>
                        <br><?= $detailError ?>
                    <?php
                    }
                } ?>
            </div>
            <?php
            } ?>
        </div>
        <?php
        }


        if (!$hasFatalErrors && !$hasWarnErrors && ($hasUpdatedConfigFile || $hasSaneConfigFile || $configFilePresent)) { ?>
            <div class="alert alert-success">
                <?= TEXT_ERROR_SUCCESS_NO_ERRORS ?>
            </div>
        <?php
        } ?>
        <?php
        if (!$hasFatalErrors && !$hasSaneConfigFile) { ?>
            <input type="submit" class="zc-full btn btn-primary" id="btnsubmit" name="btnsubmit" value="<?= TEXT_CONTINUE ?>" <?= ($hasMultipleAdmins) ? '' : 'autofocus="autofocus"' ?> tabindex="1">
        <?php
        } ?>
        <?php
        if (!$hasFatalErrors && $hasSaneConfigFile && !$hasUpgradeErrors && !$isCurrentDb && $hasUpdatedConfigFile && $hasTables) { ?>
            <input type="submit" class="zc-upg btn btn-primary" id="btnsubmit" name="btnsubmit" value="<?= TEXT_UPGRADE ?>" tabindex="2" title="<?= TEXT_UPGRADE_INFO ?>">
        <?php
        } ?>
        <?php
        if (!$hasFatalErrors && $hasSaneConfigFile && !$hasUpgradeErrors && $hasUpdatedConfigFile) { ?>
            <input type="submit" class="zc-full btn btn-primary" id="btnsubmit1" name="btnsubmit" value="<?= TEXT_CLEAN_INSTALL ?>" tabindex="3" title="<?= TEXT_CLEAN_INSTALL_INFO ?>">
        <?php
        } ?>
        <?php
        if ($hasUpgradeErrors && $hasSaneConfigFile && $hasUpdatedConfigFile) { ?>
            <input type="submit" class="zc-full btn btn-primary" id="btnsubmit2" name="btnsubmit" value="<?= TEXT_CLEAN_INSTALL ?>" tabindex="4" title="<?= TEXT_CLEAN_INSTALL_INFO ?>">
        <?php
        } ?>
        <?php
        } ?>
        <?php
        if (!$hasUpdatedConfigFile && $hasSaneConfigFile) { ?>
            <input type="hidden" name="updateConfigure" value="true">
            <input type="submit" class="zc-admin btn btn-primary" id="btnsubmit2" name="btnsubmit" value="<?= TEXT_UPDATE_CONFIGURE ?>" tabindex="4">
        <?php
        } ?>
        <?php
        if ($hasMultipleAdmins) { ?>
            <input type="submit" class="zc-admin btn btn-primary" id="btnsubmit" name="btnsubmit" value="<?= TEXT_REFRESH ?>" autofocus="autofocus" tabindex="5">
        <?php
        } else { ?>
            <?php
            if ($hasFatalErrors || $hasWarnErrors) { ?>
                <a href="" class="btn btn-secondary"><?= TEXT_REFRESH ?></a>
            <?php
            } ?>
        <?php
        } ?>
        <br style="clear:both">
</form>

<script>
    $(function () {
        $(".zc-full").click(function () {
                $('#systemCheck').attr('action', "index.php?main_page=system_setup");
                $('#systemCheck').submit();
            }
        )
        $(".zc-upg").click(function () {
                $('#systemCheck').attr('action', "index.php?main_page=database_upgrade");
                $('#systemCheck').submit();
            }
        )
        $(".zc-admin").click(function () {
                $('#systemCheck').attr('action', "index.php?main_page=index");
                $('#systemCheck').submit();
            }
        )
        $('#adminDirSelect').change(function () {
                $('#systemCheck').submit();
            }
        )
    })
</script>
