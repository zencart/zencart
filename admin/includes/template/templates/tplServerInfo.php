<?php
/**
 * Admin Lead Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
//print_r($tplVars['versionInfo']);
?>
<p class="center"><?php echo TEXT_DATABASE_QUICKLINK; ?></p>
<hr />

<section class="row">
<div class="col-lg-4 col-lg-offset-2">
    <?php foreach ($tplVars['systemInfo']['left'] as $systemInfo) { ?>
        <p><strong><?php echo $systemInfo['title']; ?></strong>&nbsp;<?php echo $systemInfo['content'];?></p>
    <?php } ?>
</div>
<div class="col-lg-4">
    <?php foreach ($tplVars['systemInfo']['right'] as $systemInfo) { ?>
        <p><strong><?php echo $systemInfo['title']; ?></strong>&nbsp;<?php echo $systemInfo['content'];?></p>
    <?php } ?>
</div>
</section>


<section class="row">
<div class="col-lg-8 col-lg-offset-2">
    <?php foreach ($tplVars['versionInfo'] as $versionInfo) { ?>
        <div class="row">
        <p class="col-lg-6 col-lg-offset-3"><?php echo $versionInfo; ?></p>
        </div>
    <?php } ?>
</div>
</section>


<section class="row">
<div class="col-lg-8 col-lg-offset-2">
<?php if ($tplVars['hasPHPInfo']) { ?>
<?php echo $tplVars['cachedPHPInfo']; ?>
<?php } else { ?>
<?php phpinfo(); ?>
<?php } ?>
</div>
</section>

<section class="row" id="database-info">
    <h2 class="center"><?php echo $tplVars['databaseInfo']['heading']; ?></h2>
    <div class="col-lg-8 col-lg-offset-2">
        <div class="center">
            <table class="table table-bordered table-striped" style="table-layout: fixed;word-wrap: break-word;">
<?php
foreach ($tplVars['databaseInfo']['fields'] as $databaseInfo) {
?>
                <tr>
                    <td class="e"><?php echo $databaseInfo['name']; ?></td>
                    <td class="v"><?php echo $databaseInfo['value']; ?></td>
                </tr>
<?php
}
?>
            </table>
        </div>
    </div>
</section>
