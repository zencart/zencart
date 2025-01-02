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
<nav class="navbar navbar-default">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-adm1-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar">&nbsp;</span>
      <span class="icon-bar">&nbsp;</span>
      <span class="icon-bar">&nbsp;</span>
    </button>
  </div>
  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse navbar-adm1-collapse">
    <ul class="nav navbar-nav">
          <?php foreach (zen_get_admin_menu_for_user() as $menuKey => $pages) { ?>
            <li class="dropdown">
              <a href="<?php echo zen_href_link(FILENAME_ALT_NAV) ?>" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><?php echo $menuTitles[$menuKey] ?><b class="caret">&nbsp;</b></a>
              <ul class="dropdown-menu">
                <?php foreach ($pages as $page) { ?>
                  <li><a href="<?php echo zen_href_link($page['file'], $page['params']) ?>"><?php echo $page['name'] ?></a></li>
                <?php } ?>
              </ul>
            </li>
          <?php
          }
          foreach ($upperMenuArray as $upperMenu) {
          ?>
          <li class="upperMenuItems"><a href="<?= $upperMenu['a'] . '" '. ($upperMenu['params'] ?? 'class="headerLink"') . '>' . $upperMenu['title'] ?></a></li>
          <?php
              }
              ?>
    </ul>
  </div><!-- /.navbar-collapse -->
</nav>
<?php if ($url = page_has_help()) { ?>
<div class="pull-right noprint">
  <a href="<?php echo $url; ?>" rel="noopener" target="_blank" class="btn btn-sm btn-default btn-help" role="button" title="Help">
    <i class="fa-regular fa-question fa-lg" aria-hidden="true"></i>
  </a>
</div>
<?php } ?>
