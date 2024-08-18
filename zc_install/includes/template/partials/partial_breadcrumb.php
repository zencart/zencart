<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

$is_upgrade_mode = ($_POST['upgrade_mode'] ?? 'no') === 'yes';
?>

<div class="row px-2 pb-2">
    <div class="col p-1 pt-3 mt-2">
        <nav style="--bs-breadcrumb-divider: ''" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item badge<?= ($current_page === 'index') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'index') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_SYSTEM_INSPECTION ?></span>
                </li>
                <li class="breadcrumb-divider">&raquo;</li>
                <?php
                if ($current_page === 'database_upgrade' || $is_upgrade_mode) {
                ?>
                <li class="breadcrumb-item badge<?= ($current_page === 'database_upgrade') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'database_upgrade') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_DATABASE_UPGRADE ?></span>
                </li>
                <li class="breadcrumb-divider">&raquo;</li>
                <?php
                } else { ?>
                <li class="breadcrumb-item badge<?= ($current_page === 'system_setup') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'system_setup') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_SYSTEM_SETUP ?></span>
                </li>
                <li class="breadcrumb-divider">&raquo;</li>
                <li class="breadcrumb-item badge<?= ($current_page === 'database') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'database') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_DATABASE_SETUP ?></span>
                </li>
                <li class="breadcrumb-divider">&raquo;</li>
                <li class="breadcrumb-item badge<?= ($current_page === 'admin_setup') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'admin_setup') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_ADMIN_SETUP ?></span>
                </li>
                <li class="breadcrumb-divider">&raquo;</li>
                <?php
                }
                ?>
                <li class="breadcrumb-item badge<?= ($current_page === 'completion') ? ' active text-bg-primary' : ' text-bg-secondary' ?>"<?= ($current_page === 'completion') ? ' aria-current="page"' : '' ?>>
                    <span><?= TEXT_NAVBAR_COMPLETION ?></span>
                </li>
            </ol>
        </nav>
    </div>
</div>
