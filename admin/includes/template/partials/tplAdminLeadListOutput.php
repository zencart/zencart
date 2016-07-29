<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.6.0 $
 */
?>
<div class="box-body no-padding">
<form class="form" name="lead_filter" id="lead_filter_form" action="<?php echo zen_admin_href_link($_GET['cmd'], 'action=multiEdit'); ?>"
      method="post">
    <input type="hidden" name="securityToken"
           value="<?php echo $_SESSION['securityToken']; ?>">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <?php foreach ($tplVars['leadDefinition']['listMap'] as $field) { ?>
                    <th><?php echo $tplVars['leadDefinition']['fields'][$field]['layout']['title']; ?></th>
                <?php } ?>
                <?php if ($tplVars['leadDefinition']['hasRowActions']) { ?>
                <th><?php echo TEXT_LEAD_ACTION; ?></th>
                <?php } ?>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <?php foreach ($tplVars['leadDefinition']['listMap'] as $field) { ?>
                    <th>
                        <?php if (isset($tplVars['leadDefinition']['fields'][$field]['layout']['type'])) { ?>
                            <?php require('includes/template/partials/leadInputTypes/tplLeadFilter' . ucfirst($tplVars['leadDefinition']['fields'][$field]['layout']['type']) . '.php'); ?>
                        <?php } else { ?>
                            &nbsp;
                        <?php } ?>
                    </th>
                <?php } ?>
                <?php if ($tplVars['leadDefinition']['hasRowActions']) { ?>
                <th>
                    <a href="<?php echo zen_admin_href_link($_GET['cmd'], zen_get_all_get_params(array('action'))); ?>" id="clearFiltersLink">Clear Filters</a>
                </th>
                <?php } ?>
            </tr>
        </thead>
        <tbody id="adminLeadItemRows">
        <?php require('includes/template/partials/tplAdminLeadItemRows.php'); ?>
        </tbody>
        <tfoot id="leadMultipleActions">
        <?php require('includes/template/partials/tplAdminLeadMultipleActions.php'); ?>
        </tfoot>
    </table>
</form>
</div>
