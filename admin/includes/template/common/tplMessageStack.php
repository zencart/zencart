<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 15/05/16
 * Time: 18:30
 */
?>
<?php if ($tplVars["messageStack"]->size > 0) { ?>
    <div class="messageStack-header noprint">
        <?php echo $tplVars["messageStack"]->output(); ?>
    </div>
<?php } ?>
