<?php
/**
 * Admin Lead Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
//print_r($tplVars);
?>

<section class="content-header row">
    <h1 class="pull-left"><?php echo HEADING_TITLE; ?></h1>
</section>
<section class="row" id="adminInfoContainer">
    <?php require 'includes/template/templates/'.$tplVars['contentTemplate']; ?>
</section>
