<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 *
 * @var \Zencart\Filters\FilterManager $filterManager
 * @var \Zencart\ViewBuilders\SimpleDataFormatter $formatter
 * @var \Zencart\TableViewControllers\BaseController $tableController
 * @var string $PHP_SELF
 */

use Zencart\Paginator\LaravelPaginator;

?>
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <?php if ($filterManager->hasFilters()) { ?>
    <div class="row noprint">
        <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
                <form method="post" action="<?php echo zen_href_link($PHP_SELF); ?>">
                    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
                    <?php foreach ($filterManager->getFilters() as $filter) { ?>
                        <?php echo $filter->output(); ?>
                    <?php } ?>
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
                        <th class="<?php echo $colHeader['headerClass']; ?>"><?php echo $colHeader['title'];
                            ?></th>
                    <?php } ?>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($formatter->getTableData() as $tableData) { ?>
                    <?php if ($formatter->isRowSelected($tableData)) { ?>
                        <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo $formatter->getSelectedRowLink(
                            $tableData); ?>'" role="button">
                    <?php } else { ?>
                        <tr class="dataTableRow" onclick="document.location.href='<?php echo
                        $formatter->getNotSelectedRowLink($tableData); ?>'"
                        role="button">
                    <?php } ?>
                    <?php foreach ($tableData as $column) { ?>
                        <td class="<?php echo $column['class']; ?>">
                            <?php echo $column['value']; ?>
                        </td>
                    <?php } ?>
                    <?php require(DIR_WS_TEMPLATES . 'partials/tableview_rowactions.php'); ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            if (!empty($tableController->getBoxHeader()) && !empty($tableController->getBoxContent())) {
                $box = new box;
                echo $box->infoBox($tableController->getBoxHeader(), $tableController->getBoxContent());
            }
            ?>
        </div>
    </div>
    <div class="row">
        <table class="table">
            <tr>
                <td><?php echo sprintf(TEXT_DISPLAY_NUMBER_OF_GENERIC, $formatter->getResultSet()->firstItem(), $formatter->getResultSet()->lastItem(), $formatter->getResultSet()->total()); ?></td>
                <td class="text-right"> <?php echo (new LaravelPaginator($formatter->getResultSet()))->display_links($formatter->getResultSet()->total(), MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'] ?? 1); ?></td>
            </tr>
        </table>
    </div>

    <?php if ($formatter->hasButtonActions()) { ?>
    <div class="row">
        <?php foreach ($formatter->getButtonActions() as $buttonAction) { ?>
            <a href="<?php echo zen_href_link($PHP_SELF, $buttonAction['hrefLink']); ?>">
            <button class="btn <?php echo $buttonAction['buttonClass']; ?>" type="button"><?php echo $buttonAction['title']; ?></button>
            </a>
        <?php } ?>
    </div>
    <?php } ?>
