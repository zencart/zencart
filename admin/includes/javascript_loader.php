<?php
/**
 * This file is inserted at the start of the body tag, just above the header menu, and loads most of the admin javascript components
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script title="jQuery check">window.jQuery || document.write('<script src="includes/javascript/jquery.min.js"><\/script>');</script>

<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js" integrity="sha256-Fb0zP4jE3JHqu+IBB9YktLcSjI1Zc6J2b6gTjB0LpoM=" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
<!--<script src="includes/javascript/bootstrap.min.js"></script>-->

<script src="includes/javascript/jquery-ui-i18n.min.js"></script>
<script title="jQuery plugin initializations">
// init datepicker defaults with localization
  jQuery(function () {
    jQuery.datepicker.setDefaults(jQuery.extend({}, jQuery.datepicker.regional["<?= $_SESSION['languages_code'] == 'en' ? '' : $_SESSION['languages_code'] ?>"], {
      dateFormat: '<?= DATE_FORMAT_DATE_PICKER ?>',
      changeMonth: true,
      changeYear: true,
      showOtherMonths: true,
      selectOtherMonths: true,
      showButtonPanel: true
    }));
    jQuery('[data-toggle="tooltip"]').tooltip({
        html: true,
        container: 'body'
    });
  });
</script>
<?php
$searchBoxScriptArray = [
    'specials',
    'coupon_admin',
    'reviews',
    'featured',
    'customers',
    'category_product_listing',
    'downloads_manager',
];
$searchBoxJs = 'includes/javascript/searchBox.js';
if (in_array(basename($PHP_SELF, '.php'), $searchBoxScriptArray) && file_exists($searchBoxJs)) {
?>
<script defer src="<?= zen_add_filemtime($searchBoxJs) ?>"></script>
<?php
}
?>

<?php if (file_exists($jsFile = 'includes/javascript/' . basename($PHP_SELF, '.php') . '.js')) { ?>
<script src="<?= zen_add_filemtime($jsFile) ?>"></script>
<?php
}
if (file_exists($jsFile = 'includes/javascript/' . basename($PHP_SELF, '.php') . '.php')) {
    echo "\n";
    require 'includes/javascript/' . basename($PHP_SELF, '.php') . '.php';
}
$directory_array = $template->get_template_part('includes/javascript/', '/^' . basename($PHP_SELF, '.php') . '_/', '.js');
foreach ($directory_array as $key => $value) {
    echo "\n";
?>
<script src="<?= zen_add_filemtime('includes/javascript/' . $value) ?>"></script>
<?php
}
$directory_array = $template->get_template_part('includes/javascript/', '/^' . basename($PHP_SELF, '.php') . '_/', '.php');
foreach ($directory_array as $key => $value) {
    echo "\n";
    require 'includes/javascript/' . $value;
}

foreach ($installedPlugins as $plugin) {
    if (is_object($plugin) && method_exists($plugin, 'getRelativePath') && method_exists($plugin, 'getAbsolutePath')) {
        $relativeDir = $plugin->getRelativePath();
        $absoluteDir = $plugin->getAbsolutePath();
    } else {
        $pluginKey = $plugin['unique_key'] ?? '';
        $pluginVersion = $plugin['version'] ?? '';
        $relativeDir = ($GLOBALS['request_type'] === 'SSL' ? DIR_WS_HTTPS_CATALOG : DIR_WS_CATALOG)
            . 'zc_plugins/' . $pluginKey . '/' . $pluginVersion . '/';
        $absoluteDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $pluginVersion . '/';
    }
    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/javascript/', '/^global_jscript/', '.php');
    foreach ($directory_array as $key => $value) {
        require $absoluteDir . 'admin/includes/javascript/' . $value;
    }
    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/javascript/', '/^global_jscript/', '.js');
    foreach ($directory_array as $key => $value) {
        echo "\n";
        $value = 'admin/includes/javascript/' . $value;
        ?>
        <script src="<?= zen_add_filemtime($relativeDir . $value, $absoluteDir . $value) ?>"></script>
        <?php
    }
    if (file_exists($absoluteDir . 'admin/includes/javascript/' . basename($PHP_SELF, '.php') . '.php')) {
        echo "\n";
        require $absoluteDir . 'admin/includes/javascript/' . basename($PHP_SELF, '.php') . '.php';
    }
    if (file_exists($absoluteDir . 'admin/includes/javascript/' . basename($PHP_SELF, '.php') . '.js')) {
        echo "\n";
        $value = 'admin/includes/javascript/' . basename($PHP_SELF, '.php') . '.js';
?>
        <script src="<?= zen_add_filemtime($relativeDir . $value, $absoluteDir . $value) ?>"></script>
<?php
    }
    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/javascript/', '/^' . basename($PHP_SELF, '.php') . '_/', '.js');
    foreach ($directory_array as $key => $value) {
        echo "\n";
        $value = 'admin/includes/javascript/' . $value;
        ?>
        <script src="<?= zen_add_filemtime($relativeDir . $value, $absoluteDir . $value) ?>"></script>
        <?php
    }
    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/javascript/', '/^' . basename($PHP_SELF, '.php') . '_/', '.php');
    foreach ($directory_array as $key => $value) {
        echo "\n";
        require $absoluteDir . 'admin/includes/javascript/' . $value;
    }}
if (file_exists(DIR_WS_INCLUDES . 'keepalive_module.php')) {
    echo "\n";
    require(DIR_WS_INCLUDES . 'keepalive_module.php');
}
echo "\n";
require DIR_FS_CATALOG . 'includes/templates/template_default/jscript/jscript_framework.php';
