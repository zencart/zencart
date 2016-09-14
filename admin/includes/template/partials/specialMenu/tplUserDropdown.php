<?php
/**
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<li class="dropdown user user-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img class="img-circle" src="<?php echo $tplVars['user']['adminGravatar']; ?>" alt="User Image" height="20">
        <span class="hidden-xs"><?php echo $tplVars['user']['adminName']; ?></span>
    </a>
    <ul class="dropdown-menu">
        <!-- User image -->
        <li class="user-header">
            <img class="img-circle" src="<?php echo $tplVars['user']['adminGravatar']; ?>" alt="User Image">
            <p><?php echo $tplVars['user']['adminName']; ?></p>
            <p></p>
        </li>
        <!-- Menu Body -->
        <li class="user-body bg-blue-active">
            <div class="row">
                <div class="col-sm-6 pull-left">
                    <a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT); ?>" class="btn btn-default"><?php echo HEADER_TITLE_ACCOUNT; ?></a>
                </div>
                <div class="col-sm-6 pull-right">
                    <a href="<?php echo zen_admin_href_link(FILENAME_LOGOFF); ?>" class="btn btn-default"><?php echo HEADER_TITLE_LOGOFF; ?></a>
                </div>
            </div>
        </li>
    </ul>
</li>

