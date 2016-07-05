<?php
//print_r($tplVars['user']);
?>
<!-- Main Header -->
<header class="main-header">
    <section>
        <a href="<?php echo zen_href_link(FILENAME_DEFAULT); ?>">
            <?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT); ?>
        </a>
    </section>
    <section>
        <?php require('includes/template/common/tplMessageStack.php'); ?>
    </section>
    <nav class="navbar navbar-default no-margin upperMenu">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
            <?php
            if (!$tplVars['hide_languages']) { ?>
                <?php
                echo zen_draw_form('languages', $tplVars['cmd'], '', 'get', 'class="navbar-form"');
                echo DEFINE_LANGUAGE . '&nbsp;&nbsp;' . (sizeof($tplVars['languages']) > 1 ? zen_draw_pull_down_menu('language', $tplVars['languages_array'], $tplVars['languages_selected'], 'onChange="this.form.submit();"') : '');
                echo zen_hide_session_id();
                echo zen_post_all_get_params(array('language'));
                echo '</form>';
                }
                ?>
            <?php require('includes/template/partials/specialMenu/tplServerInfo.php'); ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php require('includes/template/partials/specialMenu/tplStandardLinks.php'); ?>
                <?php require('includes/template/partials/specialMenu/tplNotificationsBell.php'); ?>
                <?php require('includes/template/partials/specialMenu/tplUserDropdown.php'); ?>
                <li><a title="<?php echo HEADER_TITLE_LOGOFF ?>" href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><i class="fa fa-sign-out text-white"></i></a></li>
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<?php require('includes/template/common/tplMainMenu.php'); ?>
