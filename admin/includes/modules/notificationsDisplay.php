<?php
/**
* @package admin
* @copyright Copyright 2003-2018 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: Drbyte Thu Dec 6 14:42:02 2018 -0500 New in v1.5.6 $
*/

if (! count($availableNotifications)) {
    return;
}
foreach ($availableNotifications as $nKey => $aNotification) {
    if (isset($aNotification['banner-group'])) {
?>
        <div class="row alert alert-dismissible notification-alert" role="alert" data-notification="<?php echo $nKey; ?>">
<?php if ($aNotification['can-forget']) { ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<?php } ?>
            <script>(function (d) {
                let me = d.currentScript || (function(){let s=d.getElementsByTagName("script");return s[s.length-1];})();
                let rd = Math.floor(Date.now()/60000) + "-" + Math.floor(Math.random()*1000);
                let s = d.createElement("script");
                s.src = "https://pan.zen-cart.com/display/group/" + "<?= (int)$aNotification['banner-group'] ?>" + "/?rd=" + encodeURIComponent(rd);
                s.async = true;
                me.parentNode.insertBefore(s, me.nextSibling);
                })(document);
            </script>
        </div>
<?php
        }
        if (isset($aNotification['banner-html'])) {
?>
        <div class="row alert alert-dismissible notification-alert" role="alert" data-notification="<?php echo $nKey; ?>">
            <?php if ($aNotification['can-forget']) { ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php } ?>
        <?php echo $aNotification['banner-html']; ?>
        </div>
<?php
        }
    }
?>
<script>
    $('.notification-alert').on('close.bs.alert', function () {
            zcJS.ajax({
                url: "ajax.php?act=ajaxAdminNotifications&method=forget",
                data: {'key': $(this).data('notification'), 'admin_id': <?php echo $_SESSION['admin_id']; ?>}
            }).done(function( response ) {
            });
     })
</script>
