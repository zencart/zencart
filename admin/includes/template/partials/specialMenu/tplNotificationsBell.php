<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>

<?php if (isset($tplVars['notifications']['bell'])) { ?>
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        <span class="label label-warning"><?php echo count($tplVars['notifications']['bell']); ?></span>
    </a>
    <ul class="dropdown-menu">
        <li class="header"><?php echo sprintf(TEXT_HEADER_NOTIFICATIONS_COUNT, count($tplVars['notifications']['bell'])); ?></li>
        <li>
                <?php foreach ($tplVars['notifications']['bell'] as $notification) { ?>
                <li>
                    <a href="<?php echo $notification['link']; ?>">
                        <i class="<?php echo $notification['class']; ?>"></i><?php echo $notification['text']; ?>
                    </a>
                </li>

                <?php } ?>
         </li>
    </ul>
</li>
<?php } ?>
