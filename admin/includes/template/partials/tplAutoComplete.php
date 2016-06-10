<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 03/05/16
 * Time: 17:42
 */
?>
<?php if ($tplVars['leadDefinition']['action'] != 'list') { ?>
    <?php   if ($tplVars['leadDefinition']['fields'][$field]['autocomplete']) { ?>
        <?php     if (!isset($tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom'])) { ?>
            <?php       require('includes/template/javascript/select2DriverStandard.php'); ?>
        <?php     } else { ?>
            <?php       require('includes/template/javascript/'.$tplVars['leadDefinition']['fields'][$field]['autocomplete']['custom']); ?>
        <?php     } ?>
    <?php   } ?>
<?php } ?>
