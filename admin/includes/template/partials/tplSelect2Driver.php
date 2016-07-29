<?php
/**
 * Admin Lead Template  - autocomplete partial
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<?php   if ($tplVars['leadDefinition']['fields'][$field]['fillByLookup']) { ?>
    <?php     if (!isset($tplVars['leadDefinition']['fields'][$field]['fillByLookup']['custom'])) { ?>
        <?php       require('includes/template/javascript/select2DriverStandard.php'); ?>
    <?php     } else { ?>
        <?php       require('includes/template/javascript/'.$tplVars['leadDefinition']['fields'][$field]['fillByLookup']['custom']); ?>
    <?php     } ?>
<?php   } ?>
