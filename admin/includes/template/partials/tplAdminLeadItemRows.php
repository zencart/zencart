<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
//print_r($tplVars['leadDefinition']);
?>
<?php foreach ($tplVars['listingBox']['formattedItems'] as $row) { ?>
    <tr>
        <?php if ($tplVars['leadDefinition']['showMultiActions']) { ?>
            <td>
                <input type="checkbox" class="leadMultiSelect" name="leadMultiSelect[]" value="
                <?php echo $row[$tplVars['leadDefinition']['mainTableFkeyField']]; ?>">
            </td>
        <?php } else { ?>
            <td>&nbsp;</td>
        <?php } ?>
        <?php foreach ($tplVars['leadDefinition']['listMap'] as $field) { ?>
            <td><?php echo $row[$field]; ?></td>
        <?php } ?>
        <?php if ($tplVars['leadDefinition']['hasRowActions']) { ?>
        <td class="actions">
            <?php foreach ($row['rowActions'] as $rowAction) { ?>
                <a class="btn btn-xs" href="<?php echo $rowAction['link']; ?>" <?php echo $rowAction['linkParameters']; ?>>
                    <?php echo $rowAction['linkText']; ?>
                </a>
            <?php } ?>
            &nbsp;
        </td>
        <?php } ?>
    </tr>
<?php } ?>
<?php require 'includes/template/partials/' . $tplVars['leadDefinition']['deleteItemHandlerTemplate']; ?>

