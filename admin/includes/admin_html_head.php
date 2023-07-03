<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Feb 06 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
?>
<meta charset="<?php echo CHARSET; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo TITLE; ?></title>
<?php if (file_exists($value = 'includes/css/bootstrap.min.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
<?php } else { ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
<?php } ?>
<?php if (file_exists($value = 'includes/fontawesome/css/fontawesome.min.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
    <link rel="stylesheet" href="includes/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="includes/fontawesome/css/regular.min.css">
    <?php if (FONTAWESOME_V4_SHIM == 'true' && file_exists($value = 'includes/fontawesome/css/v4-shims.min.css')) { ?>
        <link rel="stylesheet" href="<?php echo $value; ?>">
    <?php } ?>
<?php } else { ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <?php if (FONTAWESOME_V4_SHIM == 'true') { ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/v4-shims.css" crossorigin="anonymous">
    <?php } ?>
<?php } ?>
<?php if (file_exists($value = 'includes/css/jquery-ui.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
<?php } else { ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<?php } ?>
    <link rel="stylesheet" href="includes/css/jAlert.css">
    <link rel="stylesheet" href="includes/css/menu.css">
    <link rel="stylesheet" href="includes/css/stylesheet.css">
<?php if (file_exists($value = 'includes/css/' . basename($PHP_SELF, '.php') . '.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
<?php
}

$page_base_name = basename($PHP_SELF, '.php');

foreach ($installedPlugins as $plugin) {
    $relativeDir = $plugin->getRelativePath();
    $absoluteDir = $plugin->getAbsolutePath();
    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/css/', '/^global_stylesheet/', '.css');
    foreach ($directory_array as $key => $value) {
?>
        <link rel="stylesheet" href="<?php echo $relativeDir . 'admin/includes/css/' . $value; ?>">
<?php
    }

    if (file_exists($absoluteDir  . 'admin/includes/css/' . $page_base_name . '.css')) {
?>
        <link rel="stylesheet" href="<?php echo $relativeDir . 'admin/includes/css/' . $page_base_name . '.css'; ?>">
<?php
    }

    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/css/', '/^' . $page_base_name . '_/', '.css');
    foreach ($directory_array as $key => $value) {
?>
        <link rel="stylesheet" href="<?php echo $relativeDir . 'admin/includes/css/' . $value; ?>">
<?php
    }

    $directory_array = $template->get_template_part($absoluteDir . 'admin/includes/css/', '/^' . $page_base_name . '_/', '.php');
    foreach ($directory_array as $key => $value) {
        echo "\n";
        require $absoluteDir . 'admin/includes/css/' . $value;
    }
}

$directory_array = $template->get_template_part('includes/css/', '/^' . $page_base_name . '_/', '.css');
foreach ($directory_array as $key => $value) {
?>
    <link rel="stylesheet" href="includes/css/<?php echo $value; ?>">
<?php
}

$directory_array = $template->get_template_part('includes/css/', '/^' . $page_base_name . '_/', '.php');
foreach ($directory_array as $key => $value) {
    echo "\n";
    require 'includes/css/' . $value;
}

// -----
// Enable site-specific styling.
//
if (file_exists('includes/css/site-specific-styles.php')) {
    echo "\n";
    require 'includes/css/site-specific-styles.php';
}

// pull in any necessary JS for the page
require DIR_WS_INCLUDES . 'javascript_loader.php';
