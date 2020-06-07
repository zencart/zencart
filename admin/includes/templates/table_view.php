<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <div class="row noprint">
        <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
                <thead>
                <tr class="dataTableHeadingRow">
                    <?php foreach ($tableController->getTableData('headerInfo') as $colHeader) { ?>
                        <th class="<?php echo $colHeader['headerClass']; ?>"><?php echo $colHeader['title'];
                            ?></th>
                    <?php } ?>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tableController->getTableData('contentInfo') as $tableData) { ?>
                    <?php if ($tableController->tableRowSelected($tableData)) { ?>
                        <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo $tableController->getSelectedRowLink(
                            $tableData); ?>'" role="button">
                    <?php } else { ?>
                        <tr class="dataTableRow" onclick="document.location.href='<?php echo
                        $tableController->getNotSelectedRowLink($tableData); ?>'"
                        role="button">
                    <?php } ?>
                    <?php foreach ($tableData['cols'] as $colData) { ?>
                        <td class="<?php echo $colData['columnClass']; ?>">
                            <?php echo $colData['value']; ?>
                        </td>
                    <?php } ?>
                    <td class="dataTableContent text-right">
                        <?php if ($tableController->tableRowSelected($tableData)) { ?>
                            <?php echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); ?>
                        <?php } else { ?>
                            <a href="<?php echo $tableController->getNotSelectedRowLink(
                                $tableData); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO); ?></a>
                        <?php } ?>
                    </td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            if ((zen_not_null($tableController->getTableConfigBoxHeader())) && (zen_not_null
                ($tableController->getTableConfigBoxContent()))) {
                $box = new box;
                echo $box->infoBox($tableController->getTableConfigBoxHeader(), $tableController->getTableConfigBoxContent());
            }
            ?>
        </div>


        <div class="row">
            <table class="table">
                <tr>
                    <td>
                    <?php echo $tableController->getSplitPage()->display_count(TEXT_DISPLAY_NUMBER_OF_GENERIC); ?>
                    </td>
                    <td class="text-right">
                    <?php echo $tableController->getSplitPage()->display_links(MAX_DISPLAY_SEARCH_RESULTS, $tableController->getPage()); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
