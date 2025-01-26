<?php
/**
 * Common Template - tpl_footer.php
 *
 * this file can be copied to /templates/your_template_dir/pagename
 * example: to override the privacy page
 * make a directory /templates/my_template/privacy
 * copy /templates/templates_defaults/common/tpl_footer.php to /templates/my_template/privacy/tpl_footer.php
 * to override the global settings and turn off the footer un-comment the following line:
 *
 * $flag_disable_footer = true;
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 30 Modified in v2.1.0 $
 */
require DIR_WS_MODULES . zen_get_module_directory('footer.php');

if (!isset($flag_disable_footer) || !$flag_disable_footer) {
?>
<!--bof-navigation display -->
<div id="navSuppWrapper">
    <div id="navSupp">
        <ul>
            <li><a href="<?= HTTP_SERVER . DIR_WS_CATALOG ?>"><?= HEADER_TITLE_CATALOG ?></a></li>
<?php
    if (EZPAGES_STATUS_FOOTER === '1' || (EZPAGES_STATUS_FOOTER === '2' && zen_is_whitelisted_admin_ip())) {
        require $template->get_template_dir('tpl_ezpages_bar_footer.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_ezpages_bar_footer.php';
    }
?>
        </ul>
    </div>
</div>
<!--eof-navigation display -->
<?php
    // -----
    // Add notification for plugin content insertion.
    //
    $zco_notifier->notify('NOTIFY_FOOTER_AFTER_NAVSUPP', []);
?>
<!--bof-ip address display -->
<?php
    if (SHOW_FOOTER_IP === '1') {
?>
<div id="siteinfoIP"><?= TEXT_YOUR_IP_ADDRESS . ' ' . $_SERVER['REMOTE_ADDR'] ?></div>
<?php
    }
?>
<!--eof-ip address display -->

<!--bof-banner #5 display -->
<?php
    if (SHOW_BANNERS_GROUP_SET5 != '' && $banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET5)) {
        if (!$banner->EOF) {
?>
<div id="bannerFive" class="banners"><?= zen_display_banner('static', $banner) ?></div>
<?php
        }
    }
?>
<!--eof-banner #5 display -->

<!--bof- site copyright display -->
<div id="siteinfoLegal" class="legalCopyright"><?= FOOTER_TEXT_BODY ?></div>
<!--eof- site copyright display -->

<?php
} // flag_disable_footer

if (false || !empty($showValidatorLink)) {
?>
<a href="https://validator.w3.org/nu/?doc=<?= urlencode('http' . ($request_type == 'SSL' ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . (strstr($_SERVER['REQUEST_URI'], '?') ? '&' : '?') . zen_session_name() . '=' . zen_session_id()) ?>" rel="noopener" target="_blank">VALIDATOR</a>
<?php
}
