<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 03/05/16
 * Time: 16:58
 */
?>
<?php if ($tplVars['leadDefinition']['action'] != 'list') { ?>
    <label class="col-sm-4 control-label" for="<?php echo $tplVars['leadDefinition']['fields'][$field]['field']; ?>">
        <?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['title']; ?>
    </label>
<?php } ?>

