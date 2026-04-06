<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */
if (!defined('IS_ADMIN_FLAG')) die('Illegal Access');

$menuTitles = zen_get_menu_titles();
$adminMenu  = zen_get_admin_menu_for_user();
?>
<nav class="navbar navbar-default main-tier">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-adm1-collapse">
                <span class="sr-only"><?= HEADER_TOGGLE_NAVIGATION ?></span>
                <i class="fa fa-bars"></i> <?= HEADER_TITLE_MENU ?>
            </button>
            <a class="navbar-brand visible-xs" href="#"><?= HEADER_TITLE_MENU ?></a>
        </div>

        <div class="collapse navbar-collapse navbar-adm1-collapse">
            <ul class="nav navbar-nav">

                <?php
                // if menu is empty, show a warning
                if (empty($adminMenu)) {
                    echo '<li><a href="#" style="color:red;">' . HEADER_TITLE_MENU_ERROR . '</a></li>';
                }

                foreach ($adminMenu as $menuKey => $pages) {
                    ?>
                    <li class="dropdown">
                        <a href="<?= zen_href_link(FILENAME_ALT_NAV) ?>" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= $menuTitles[$menuKey] ?> <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($pages as $page) { ?>
                                <li>
                                    <a href="<?= zen_href_link($page['file'], $page['params']) ?>">
                                        <?= $page['name'] ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php
                if (!empty($upperMenuArray)) {
                    ?>
                    <li class="dropdown visible-xs">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= HEADER_TITLE_QUICK_ACTIONS ?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($upperMenuArray as $upperMenu) { ?>
                                <li><a href="<?= $upperMenu['a'] ?>" <?= (isset($upperMenu['params']) ? $upperMenu['params'] : '') ?>><?= $upperMenu['title'] ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

            </ul>

            <?php if ($url = page_has_help()) { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="<?= $url ?>" rel="noopener" target="_blank" class="text-info" title="<?= IMAGE_MODULE_HELP ?>">
                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i> <span class="hidden-sm"><?= IMAGE_MODULE_HELP ?></span>
                        </a>
                    </li>
                </ul>
            <?php } ?>

        </div>
    </div>
</nav>
