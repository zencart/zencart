<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php if ($tplVars['listingBox']['paginator']['totalItemCount'] && $tplVars['leadDefinition']['showMultiActions'] ) { ?>
<tr>
  <td colspan="<?php echo $tplVars['leadDefinition']['columnCount']; ?>">&nbsp;<input
    type="checkbox" id="adminLeadMultiCheckbox"><?php echo TEXT_MULTIPLE_CHECKBOX_TEXT; ?><?php if ($tplVars['leadDefinition']['multiEdit']) { ?><a
    href="#"><i class="fi-pencil" id="adminLeadMultiEdit"
      style="font-size: 2em" ></i></a><?php } ?>&nbsp;<?php if ($tplVars['leadDefinition']['allowMultiDelete']) { ?><a
    href="#"><i class="fa fa-trash" id="adminLeadMultiDelete" title="<?php echo TEXT_MULTI_DELETE; ?>"
      </i></a><?php } ?></td>
</tr>
<?php } ?>
