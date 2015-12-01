<?php
/**
 * @package Admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php if ($tplVars['listingBox']['paginator']['totalItemCount'] && $tplVars['leadDefinition']['showMultiActions']) { ?>
    <tr>
        <td colspan="<?php echo $tplVars['leadDefinition']['columnCount']; ?>">
            <label><input type="checkbox" id="adminLeadMultiCheckbox"> <?php echo TEXT_MULTIPLE_CHECKBOX_TEXT; ?></label>
            <?php if ($tplVars['leadDefinition']['multiEdit']) { ?>
                <a href="#"><i class="fa fa-2x fa-pencil" id="adminLeadMultiEdit"></i></a>
            <?php } ?>
            &nbsp;<?php if ($tplVars['leadDefinition']['allowMultiDelete']) { ?>
                <a href="#" id="adminLeadMultiDelete">
                    <i class="fa fa-2x fa-trash"></i> <?php echo TEXT_MULTI_DELETE; ?>
                </a>
            <?php } ?>
        </td>
    </tr>
<?php } ?>
