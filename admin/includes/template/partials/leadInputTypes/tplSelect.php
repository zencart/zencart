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
<select
  class="<?php echo $tplVars['leadDefinition']['action']; ?>LeadFilterInput"
  style="width: auto" name="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>">
<?php foreach ($tplVars['leadDefinition']['fields'][$field]['layout']['options'] as $option) { ?>
<option
    value="<?php echo htmlspecialchars($option['id'], ENT_COMPAT, CHARSET, FALSE); ?>"
    <?php if ($tplVars['leadDefinition']['fields'][$field]['value'] == $option['id']) echo ' selected="selected" '; ?>>
    <?php echo htmlspecialchars($option['text'], ENT_COMPAT, CHARSET, FALSE); ?>
</option>
<?php } ?>
</select>
