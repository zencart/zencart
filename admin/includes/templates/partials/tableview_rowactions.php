<td class="dataTableContent text-right">
    <?php if ($formatter->hasRowActions()) { ?>
        <?php foreach ($formatter->getRowActions($tableData) as $rowAction) { ?>
            <?php //dump($rowAction); ?>
            <a href="<?php echo zen_href_link($PHP_SELF, $rowAction['hrefLink']); ?>"><i class="fa <?php echo $rowAction['icon']; ?> fa-2x"></i></a>
        <?php } ?>
    <?php } ?>
    <?php if ($formatter->isRowSelected($tableData)) { ?>
        <i class="fa-solid fa-caret-right fa-2x"></i>
    <?php } else { ?>
        <a href="<?php echo $formatter->getNotSelectedRowLink(
            $tableData); ?>"><i class="fa-solid fa-circle-info fa-2x"></i></a>
    <?php } ?>
</td>
