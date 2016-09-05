<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php foreach ($tplVars['listingBox']['formattedItems'] as $row) { ?>
    <tr>
        <?php if ($tplVars['pageDefinition']['showMultiActions']) { ?>
            <td>
                <input type="checkbox" class="leadMultiSelect" name="leadMultiSelect[]" value="
                <?php echo $row[$tplVars['pageDefinition']['mainTableFkeyField']]; ?>">
            </td>
        <?php } else { ?>
            <td>&nbsp;</td>
        <?php } ?>
        <?php foreach ($tplVars['pageDefinition']['listMap'] as $field) { ?>
            <td><?php echo $row[$field]; ?></td>
        <?php } ?>
        <?php if ($tplVars['pageDefinition']['hasRowActions']) { ?>
        <td class="actions">
            <?php foreach ($row['rowActions'] as $action => $rowAction) { ?>
                <a class="btn btn-xs <?php echo 'rowHandler'. ucfirst($action); ?>" href="<?php echo $rowAction['link']; ?>" <?php echo $rowAction['linkParameters']; ?>>
                    <?php echo $rowAction['linkText']; ?>
                </a>
            <?php } ?>
            &nbsp;
        </td>
        <?php } ?>
    </tr>
<?php } ?>
<?php if ($tplVars['listingBox']['formattedTotals'] != null) { ?>
<tr>
<?php $cols = count($tplVars['pageDefinition']['listMap']) + 1; ?>
<td class="underline" colspan="<?php echo $cols;?>"></td>
</tr>
    <tr>
        <td><?php echo TEXT_PAGE_TOTALS; ?></td>
        <?php foreach ($tplVars['listingBox']['formattedTotals'] as $field) { ?>
            <td><?php echo $field; ?></td>
        <?php } ?>
    </tr>
<?php } ?>
<?php require 'includes/template/partials/' . $tplVars['pageDefinition']['deleteItemHandlerTemplate']; ?>
