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
