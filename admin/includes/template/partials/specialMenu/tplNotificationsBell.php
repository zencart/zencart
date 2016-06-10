<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 08/05/16
 * Time: 12:47
 */
//print_r($tplVars['notifications']['bell']);
//echo count($tplVars['notifications']['bell'])
?>

<?php if (isset($tplVars['notifications']['bell'])) { ?>}
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
