<?php
/**
 * System Inspection (formerly Mod List by That Software Guy)
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Copyright 2015-2023 That Software Guy
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Sep 29 New in v2.1.0-beta1 $
 */

require 'includes/application_top.php';
?>
<!doctype html >
<html <?= HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h1><?= HEADING_TITLE ?></h1>
            <p><?= sprintf(PRESENT_ZENCART_VERSION_NUMBER, zen_get_zcversion()) ?></p>
            <p><?= HEADING_WHY ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h2><?= PAGES_TABLE ?></h2>
            <table class="table table-hover" role="listbox">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?= HEADING_PAGE_NAME ?></th>
                    <th class="dataTableHeadingContent"><?= HEADING_PAGE_MENU_KEY ?></th>
                    <th class="dataTableHeadingContent"><?= HEADING_DISPLAY ?></th>
                    <th class="dataTableHeadingContent"><?= HEADING_PAGE_LINK ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $new_pages = [];
                $sql = "SELECT ap.* FROM " . TABLE_ADMIN_PAGES . " ap
                        LEFT JOIN " . TABLE_ADMIN_MENUS . " am ON am.menu_key = ap.menu_key
                        ORDER BY am.sort_order, ap.sort_order";
                $pages = $db->Execute($sql);
                if ($pages->RecordCount() <= 0) {
                    ?>
                    <tr>
                        <td colspan="4" class="danger text-danger"><strong><?= NO_PAGES_TABLE_FOUND ?></strong></td>
                    </tr>
                    <?php
                } else {
                    $unknown_pages = 0;
                    foreach ($pages as $page) {
                        $key = $page['language_key'];
                        if (in_array($key, BUILT_IN_BOXES, true)) {
                            continue;
                        }
                        $unknown_pages++;
                        ?>
                        <tr>
                            <td class="dataTableContent">
                                <?= (defined($page['language_key'])) ? constant($page['language_key']) : "(" . $page['language_key'] . ")" ?>
                            </td>
                            <td class="dataTableContent">
                                <?= $page['menu_key'] ?>
                            </td>
                            <td class="dataTableContent">
                                <?= $page['display_on_menu'] ?>
                            </td>
                            <td class="dataTableContent">
                                <?= (defined($page['language_key']) && defined($page['main_page']))
                                    ? '<a href="' . zen_href_link(constant($page['main_page']), $page['page_params']) . '">' . constant($page['language_key']) . '</a>'
                                    : NO_LINK ?>
                            </td>
                        </tr>
                        <?php
                    }
                    if ($unknown_pages === 0) {
                        ?>
                        <tr>
                            <td colspan="4" class="success text-success"><?= NO_NEW_PAGES ?></td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <tr>
                            <td colspan="4" class="info text-info"><?= PAGES_PLUGIN_FOOTNOTE ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h2><?= PLUGINS_LIST ?> (<?= '<a href="' . zen_href_link(FILENAME_PLUGIN_MANAGER) . '">' . BOX_MODULES_PLUGINS . '</a>' ?>)</h2>
            <ul>
            <?php
            if (empty($installedPlugins)) {
                echo '<li>' . NO_PLUGINS_ENABLED . '</li>';
            }
            foreach ($installedPlugins as $plugin) {
                $url = 'https://www.zen-cart.com/downloads.php?do=file&id=' . $plugin['zc_contrib_id'];
                $link = !empty($plugin['zc_contrib_id']) ? '<a href="' . $url . '" target="_blank" rel="noopener">' . $plugin['name'] . '</a>' : $plugin['name'];
                echo '<li>' . sprintf(PLUGIN_FOUND, $link, $plugin['unique_key'], $plugin['version'], $plugin['author']) . '</li>';
            }
            ?>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h2><?= DB_LIST ?></h2>
            <?php
            $new_pages = [];
            $tables_query_raw = "SELECT TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '" . DB_DATABASE . "'";
            $tables = $db->Execute($tables_query_raw);
            if ($tables->RecordCount() <= 0) {
                ?>
                <ul>
                    <li class="danger text-danger"><strong><?= NO_INFORMATION_SCHEMA_TABLE_FOUND ?></strong></li>
                </ul>
                <?php
            } else {
                ?>
                <ul>
                    <?php
                    $unknown_tables = 0;
                    $tables_found = [];
                    foreach ($tables as $table) {
                        $key = $table['TABLE_NAME'];
                        if (DB_PREFIX !== '') {
                            $key = substr($key, strlen(DB_PREFIX));
                        }
                        $tables_found[] = $key;
                        if (in_array($key, BUILT_IN_TABLES, true) || in_array($key, OPTIONAL_TABLES, true)) {
                            continue;
                        }
                        ?>
                        <li><?= $table['TABLE_NAME'] ?></li>
                        <?php
                        $unknown_tables++;
                    }

                    if ($unknown_tables === 0) { ?>
                        <li><?= NO_NEW_TABLES ?></li>
                        <?php
                    } ?>
                </ul>
                <ul>
                    <?php
                    $missing_tables = [];
                    foreach (BUILT_IN_TABLES as $table) {
                        if (!in_array($table, $tables_found, true)) {
                        ?>
                        <li class="danger text-danger"><?= sprintf(MISSING_TABLE, $table) ?></li>
                        <?php
                        }
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h2><?= MODULE_LIST ?></h2>
            <div class="ml-4">
            <p><em><?= MODULES_SUBNOTE ?></em></p>
            <h3><?= BOX_MODULES_PAYMENT ?></h3>
            <ul>
                <?php
                $list = explode(';', MODULE_PAYMENT_INSTALLED);
                $i = 0;
                foreach ($list as $item) {
                    $message = '';
                    if (!in_array($item, BUILT_IN_PAYMENTS)) {
                        $i++;
                        $message = sprintf(NEW_MODULE_FOUND, $item);
                    }
                    if (array_key_exists($item, REPLACEMENTS)) {
                        $message = sprintf(REPLACE_MODULE_WITH, $item, REPLACEMENTS[$item]);
                    } elseif (in_array($item, OBSOLETE_PAYMENTS, true)) {
                        $message = sprintf(OBSOLETE_MODULE, $item);
                    }
                    if ($message !== '') {
                        echo '<li class="danger text-danger">' . $message . '</li> ';
                    }
                }
                if ($i === 0) {
                    echo '<li>' . NO_EXTRAS . '</li>';
                }
                ?>
            </ul>
            <h3><?= BOX_MODULES_SHIPPING ?></h3>
            <ul>
                <?php
                $list = explode(';', MODULE_SHIPPING_INSTALLED);
                $i = 0;
                foreach ($list as $item) {
                    $message = '';
                    if (!in_array($item, BUILT_IN_SHIPPINGS)) {
                        $i++;
                        $message = sprintf(NEW_MODULE_FOUND, $item);
                    }
                    if (array_key_exists($item, REPLACEMENTS)) {
                        $message = sprintf(REPLACE_MODULE_WITH, $item, REPLACEMENTS[$item]);
                    } elseif (in_array($item, OBSOLETE_SHIPPING, true)) {
                        $message = sprintf(OBSOLETE_MODULE, $item);
                    }
                    if ($message !== '') {
                        echo '<li class="danger text-danger">' . $message . '</li> ';
                    }
                }
                if ($i === 0) {
                    echo '<li>' . NO_EXTRAS . '</li>';
                }
                ?>
            </ul>
            <h3><?= BOX_MODULES_ORDER_TOTAL ?></h3>
            <ul>
                <?php
                $list = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
                $i = 0;
                foreach ($list as $item) {
                    $message = '';
                    if (!in_array($item, BUILT_IN_ORDER_TOTALS)) {
                        $i++;
                        $message = sprintf(NEW_MODULE_FOUND, $item);
                    }
                    if (array_key_exists($item, REPLACEMENTS)) {
                        $message = sprintf(REPLACE_MODULE_WITH, $item, REPLACEMENTS[$item]);
                    } elseif (in_array($item, OBSOLETE_ORDER_TOTALS, true)) {
                        $message = sprintf(OBSOLETE_MODULE, $item);
                    }
                    if ($message !== '') {
                        echo '<li class="danger text-danger">' . $message . '</li> ';
                    }
                }
                if ($i === 0) {
                    echo '<li>' . NO_EXTRAS . '</li>';
                }
                ?>
            </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-offset-1">
            <h2><?= MISSING_ADMIN_PAGES; ?></h2>
            <?php
            $missing_pages = [];
            $pages_query_raw = "SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE visible = 1";
            $pages = $db->Execute($pages_query_raw);
            foreach ($pages as $page) {
                $gid = $page['configuration_group_id'];
                $admin_entry = $db->Execute("SELECT * FROM " . TABLE_ADMIN_PAGES . " WHERE page_params = 'gid=" . (int)$gid . "'");
                if ($admin_entry->EOF) {
                    $missing_pages[] = [
                        'gid' => $gid,
                        'name' => $page['configuration_group_title'],
                    ];
                }
            }
            if (count($missing_pages) > 0) {
                ?>
                <div class="danger text-danger"><?= MISSING_ADMIN_PAGES_WHY ?></div>
                <ul>
                    <?php
                    foreach ($missing_pages as $missing_page) {
                        ?>
                        <li><?= '<a href="' . zen_href_link(FILENAME_CONFIGURATION, "gID=" . (int)$missing_page['gid']) . '">' . $missing_page['name'] . '</a>' ?></li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            } else {
                ?>
                <ul>
                    <li><?= NO_MISSING_ADMIN_PAGES ?></li>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>

</div>
<?php
require DIR_WS_INCLUDES . 'footer.php'; ?>
<br>
</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
