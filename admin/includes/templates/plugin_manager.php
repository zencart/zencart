<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 sept 25 Modified in v2.2.0-alpha $
 *
 * @var \Zencart\ViewBuilders\SimpleDataFormatter $formatter
 * @var \Zencart\TableViewControllers\BaseController $tableController
 * @var string $PHP_SELF
 */


?>
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
        <?php
            foreach ([1, 2, 0] as $i) {
                $firstheader = 0;
                $skip = 1;
                foreach ($formatter->getTableData() as $tableData) {
                    if ($tableData ["status"] ["original"] === $i) {
                        $skip = 0;
                        break;
                    }
                }
                if ($skip === 0) {
        ?>
            <table class="table table-hover">
                <thead>
                <tr class="dataTableHeadingRow">
                    <?php $firstheader = 0;
                        $colnumb = 0;
                        foreach ($formatter->getTableHeaders() as $colHeader) {
                        $colwidth = match(true) {
                            $colnumb === 0 => '',
                            $colnumb <= 1 => ' w-10',
                            $colnumb <= 2 => ' w-15',
                            $colnumb <= 3 => ' w-20',
                            default => ' w-10',
                        };
                    ?>
                        <th class="<?php echo $colHeader['headerClass'] . $colwidth; ?>">
                        <?php if ($firstheader === 0) {
                            $tabletitle = match($i) {
                                0 => TEXT_NOT_INSTALLED,
                                1 => TEXT_INSTALLED_ENABLED,
                                2 => TEXT_INSTALLED_DISABLED,
                            };
                            echo $tabletitle;
                            $firstheader = 1;
                            } else {
                                echo $colHeader['title'];
                            }
                            ?></th>
                    <?php $colnumb += 1;
                        } ?>
                    <th class="dataTableHeadingContent w-5 text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($formatter->getTableData() as $tableData) { ?>
                    <?php if ($tableData ["status"] ["original"] === $i) {
                              if ($formatter->isRowSelected($tableData)) { ?>
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
                        <?php require DIR_WS_TEMPLATES . 'partials/tableview_rowactions.php'; ?>
                        </tr>
                    <?php }
                } ?>
                </tbody>
            </table>
        <?php   }
            } ?>
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
</div>