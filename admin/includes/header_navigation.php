<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Nick Fenwick 2023 Jul 03 Modified in v2.0.0-alpha1 $
 */

if (!defined('IS_ADMIN_FLAG')) die('Illegal Access');

$menuTitles = zen_get_menu_titles();
?>

<div class="row">
<nav class="navbar navbar-expand navbar-light bg-light">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-start" id="navbarSupportedContent">
            <ul class="navbar-nav flex-wrap">
                <?php foreach (zen_get_admin_menu_for_user() as $menuKey => $pages) { ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="<?php echo zen_href_link(FILENAME_ALT_NAV) ?>" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $menuTitles[$menuKey] ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <?php foreach ($pages as $page) { ?>
                            <li><a class="dropdown-item" href="<?php echo zen_href_link($page['file'], $page['params']) ?>"><?php echo $page['name'] ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
                <?php } ?>

            </ul>
        </div>
    </div>
</nav>

<nav class="navbar navbar-default d-sm-block d-md-none">
    <ul class="nav nav-pills">

          <li class="upperMenuItems"><a href="<?php echo zen_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_TOP; ?></a></li>
          <li class="upperMenuItems"><a href="<?php echo zen_catalog_href_link(FILENAME_DEFAULT); ?>" class="headerLink" rel="noopener" target="_blank"><?php echo HEADER_TITLE_ONLINE_CATALOG; ?></a></li>
          <li class="upperMenuItems"><a href="https://www.zen-cart.com/forum" class="headerLink" rel="noopener" target="_blank"><?php echo HEADER_TITLE_SUPPORT_SITE; ?></a></li>
          <li class="upperMenuItems"><a href="<?php echo zen_href_link(FILENAME_SERVER_INFO, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_VERSION; ?></a></li>
          <li class="upperMenuItems"><a href="<?php echo zen_href_link(FILENAME_ADMIN_ACCOUNT, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_ACCOUNT; ?></a></li>
          <li class="upperMenuItems"><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
    </ul>
</nav>
</div>
<?php if ($url = page_has_help()) { ?>
<div class="float-end noprint">
  <a href="<?php echo $url; ?>" rel="noopener" target="_blank" class="btn btn-sm btn-secondary btn-help" role="button" title="Help">
    <i class="fa-regular fa-question fa-lg" aria-hidden="true"></i>
  </a>
</div>
<?php } ?>

