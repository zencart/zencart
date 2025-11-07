<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2025 Oct 06 Modified in v2.2.0 $
 *
 * @var \Zencart\Filters\FilterManager $filterManager
 * @var \Zencart\ViewBuilders\SimpleDataFormatter $formatter
 * @var \Zencart\TableViewControllers\BaseController $tableController
 * @var string $PHP_SELF
 */

use Zencart\Paginator\LaravelPaginator;

?>
<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <?php if ($filterManager->hasFilters()) { ?>
    <div class="row noprint">
        <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <form method="post" action="<?= zen_href_link($PHP_SELF) ?>">
                    <input type="hidden" name="securityToken" value="<?= $_SESSION['securityToken'] ?>">
                    <?php foreach ($filterManager->getFilters() as $filter) {
                        echo $filter->output();
                    } ?>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
                <thead>
                <tr class="dataTableHeadingRow">
                    <?php foreach ($formatter->getTableHeaders() as $colHeader) { ?>
                        <th class="<?= $colHeader['headerClass'] ?>"><?= $colHeader['title'] ?></th>
                    <?php } ?>
                    <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($formatter->getTableData() as $tableData) {
                    if ($formatter->isRowSelected($tableData)) { ?>
                        <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?= $formatter->getSelectedRowLink($tableData) ?>'" role="button">
                    <?php } else { ?>
                        <tr class="dataTableRow" onclick="document.location.href='<?= $formatter->getNotSelectedRowLink($tableData) ?>'"
                        role="button">
                    <?php }
                    foreach ($tableData as $column) { ?>
                        <td class="<?= $column['class'] ?>"><?= $column['value'] ?></td>
                    <?php }
                    require DIR_WS_TEMPLATES . 'partials/tableview_rowactions.php'; ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            if (!empty($tableController->getBoxHeader()) && !empty($tableController->getBoxContent())) {
                $box = new box();
                echo $box->infoBox($tableController->getBoxHeader(), $tableController->getBoxContent());
            }
            ?>
        </div>
    </div>
    <div class="row">
        <table class="table">
            <tr>
                <td><?= sprintf(TEXT_DISPLAY_NUMBER_OF_GENERIC, $formatter->getResultSet()->firstItem(), $formatter->getResultSet()->lastItem(), $formatter->getResultSet()->total()) ?></td>
                <td class="text-right"> <?= (new LaravelPaginator($formatter->getResultSet()))->display_links($formatter->getResultSet()->total(), MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'] ?? 1) ?></td>
            </tr>
        </table>
    </div>

    <?php if ($formatter->hasButtonActions()) { ?>
    <div class="row">
        <?php foreach ($formatter->getButtonActions() as $buttonAction) { ?>
            <a href="<?= zen_href_link($PHP_SELF, $buttonAction['hrefLink']) ?>">
            <button class="btn <?= $buttonAction['buttonClass'] ?>" type="button"><?= $buttonAction['title'] ?></button>
            </a>
        <?php } ?>
    </div>
    <?php } ?>
</div>
