<?php
/**
 * Admin Lead Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
//print_r($tplVars['newModules']);
?>

<section class="row" >
    <div class="col-lg-8 col-lg-offset-2">
    <p><?php echo HEADING_WHY .  PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . "?"; ?></p>
    </div>
</section>
<section class="row" >
    <div class="col-lg-8 col-lg-offset-2">
        <h2><?php echo PAGES_TABLE; ?></h2>
        <?php if (!$tplVars['hasAdminPages']) { ?>
            <?php echo NO_PAGES_TABLE_FOUND; ?>
        <?php } ?>
        <?php if (!$tplVars['hasFoundAdminPages']) { ?>
            <?php echo NO_NEW_PAGES; ?>
        <?php } ?>
        <?php if ($tplVars['hasAdminPages']) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th><?php echo HEADING_PAGE_NAME; ?></th>
                    <th><?php echo HEADING_PAGE_MENU_KEY; ?></th>
                    <th><?php echo HEADING_DISPLAY; ?></th>
                    <th><?php echo HEADING_PAGE_LINK; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($tplVars['newAdminPages'] as $page) { ?>
                    <?php //print_r($page); ?>
                    <tr>
                        <td><?php echo $page['name']; ?></td>
                        <td><?php echo $page['menuKey']; ?></td>
                        <td><?php echo $page['display']; ?></td>
                        <td><?php echo $page['pageLink']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
     </div>
</section>
<section class="row" >
    <div class="col-lg-8 col-lg-offset-2">
        <h2><?php echo DB_LIST; ?></h2>
        <?php if (!$tplVars['hasDBShemaTable']) { ?>
            <?php echo NO_INFORMATION_SCHEMA_TABLE_FOUND; ?>
        <?php } ?>
        <?php if (!$tplVars['hasFoundDBTables']) { ?>
            <?php echo NO_NEW_TABLES; ?>
        <?php } ?>
        <?php if ($tplVars['hasFoundDBTables']) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th><?php echo HEADING_TABLE_NAME; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($tplVars['newDBTables'] as $table) { ?>
                    <tr>
                        <td><?php echo $table; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>

    </div>
</section>
<section class="row" >
    <div class="col-lg-8 col-lg-offset-2">
        <h2><?php echo MODULE_LIST; ?></h2>
        <?php if (!$tplVars['hasFoundModules']) { ?>
            <?php echo NO_EXTRAS; ?>
        <?php } ?>
        <?php if ($tplVars['hasFoundModules']) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th><?php echo HEADING_MODULE_TYPE; ?></th>
                    <th><?php echo HEADING_MODULE_NAME; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($tplVars['newModules'] as $module) { ?>
                    <tr>
                        <td><?php echo $module['type']; ?></td>
                        <td><?php echo $module['value']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</section>
<section class="row" >
    <div class="col-lg-8 col-lg-offset-2">
        <h2><?php echo MISSING_ADMIN_PAGES; ?></h2>
        <div class="smallText"><?php echo MISSING_ADMIN_PAGES_WHY; ?></div>
        <?php if (!$tplVars['hasMissingAdminPages']) { ?>
            <?php echo NO_MISSING_ADMIN_PAGES; ?>
        <?php } ?>
        <?php if ($tplVars['hasMissingAdminPages']) { ?>
            <table class="table">
                <thead>
                <tr>
                    <th><?php echo HEADING_MISSING_ADMIN_PAGE; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($tplVars['missingPages'] as $missingPage) { ?>
                    <tr>
                        <td><?php echo'<a href="' . zen_admin_href_link(FILENAME_CONFIGURATION, "gID=" . (int)$missingPage['gid']) .'">' . $missingPage['name'] . '</a>'; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</section>
