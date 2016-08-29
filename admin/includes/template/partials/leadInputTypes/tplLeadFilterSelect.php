<?php
/**
 * Admin Lead Template  - select partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>

<div class="form-group">

    <div
        class="input-group col-sm-6 ">
        <select
            value="<?php echo htmlspecialchars($tplVars['pageDefinition']['fields'][$field]['value']); ?>"
            class="form-control <?php echo $tplVars['pageDefinition']['action']; ?>LeadFilterInput"
            name="<?php echo $tplVars['pageDefinition']['fields'][$field]['field']; ?>">
            <?php foreach ($tplVars['pageDefinition']['fields'][$field]['layout']['options'] as $option) { ?>
                <option
                    value="<?php echo zen_output_string_protected($option['id']); ?>"
                    <?php if ($tplVars['pageDefinition']['fields'][$field]['value'] == $option['id']) {
                        echo ' selected="selected" ';
                    } ?>>
                    <?php echo zen_output_string_protected($option['text']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
</div>
