<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 07/05/16
 * Time: 19:29
 */
?>
<nav class="navbar navbar-default no-margin">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"
                aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <?php foreach ($tplVars['adminMenuForUser'] as $menuKey => $pages) { ?>
                    <li class="dropdown">
                        <a href="#" role="button" aria-haspopup="true"
                           aria-expanded="false"><?php echo $tplVars['menuTitles'][$menuKey] ?><span
                                class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php foreach ($pages as $page) { ?>
                                <li><a href="<?php echo zen_href_link($page['file'],
                                        $page['params']) ?>"><?php echo $page['name'] ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
