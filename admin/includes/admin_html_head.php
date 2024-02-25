<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Feb 11 Modified in v2.0.0-beta1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Set a processing flag that indicates that this file has been loaded.  The
// flag is used by /admin/includes/header.php to warn admins and developers
// if the legacy stylesheet loading is currently in effect.  That legacy-loading
// (and this section) will be removed in a subsequent release of Zen Cart.
//
$zen_admin_html_head_loaded = true;
?>
<meta charset="<?php echo CHARSET; ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo TITLE; ?></title>
<?php if (file_exists($value = DIR_WS_INCLUDES . 'css/bootstrap.min.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
<?php } else { ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
<?php } ?>
<?php if (file_exists($value = DIR_WS_INCLUDES . 'fontawesome/css/fontawesome.min.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>fontawesome/css/regular.min.css">
    <?php if ((empty($disableFontAwesomeV4Compatibility)) &&
        file_exists($value = DIR_WS_INCLUDES . 'fontawesome/css/v4-shims.min.css')) { ?>
        <link rel="stylesheet" href="<?php echo $value; ?>">
    <?php } ?>
<?php } else { ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha256-HtsXJanqjKTc8vVQjO4YMhiqFoXkfBsjBWcX91T1jr8= sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0 sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    <?php if (empty($disableFontAwesomeV4Compatibility)) { ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/v4-shims.css" integrity="sha256-CB2v9WYYUz97XoXZ4htbPxCe33AezlF5MY8ufd1eyQ8= sha384-JfB3EVqS5xkU+PfLClXRAMlOqJdNIb2TNb98chdDBiv5yD7wkdhdjCi6I2RIZ+mL sha512-tqGH6Vq3kFB19sE6vx9P6Fm/f9jWoajQ05sFTf0hr3gwpfSGRXJe4D7BdzSGCEj7J1IB1MvkUf3V/xWR25+zvw==" crossorigin="anonymous">
    <?php } ?>
<?php } ?>
<?php if (file_exists($value = DIR_WS_INCLUDES . 'css/jquery-ui.css')) { ?>
    <link rel="stylesheet" href="<?php echo $value; ?>">
<?php } else { ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" integrity="sha256-RPilbUJ5F7X6DdeTO6VFZ5vl5rO5MJnmSk4pwhWfV8A= sha384-xewr6kSkq3dBbEtB6Z/3oFZmknWn7nHqhLVLrYgzEFRbU/DHSxW7K3B44yWUN60D sha512-/Q1sBqvNZheW2yvAccKiu/xc/o2AtDS2jNBozDEqA/8Mk/IcH853wrwDSGqAdl7jFyOWOcefLtwDd3kYo276Hw==" crossorigin="anonymous">
<?php } ?>
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>css/jAlert.css">
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>css/menu.css">
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>css/stylesheet.css">
<?php if (file_exists($value = DIR_WS_INCLUDES . 'css/' . basename($PHP_SELF, '.php') . '.css')) { ?>
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

$directory_array = $template->get_template_part(DIR_WS_INCLUDES . 'css/', '/^' . $page_base_name . '_/', '.css');
foreach ($directory_array as $key => $value) {
?>
    <link rel="stylesheet" href="<?php echo DIR_WS_INCLUDES ?>css/<?php echo $value; ?>">
<?php
}

$directory_array = $template->get_template_part(DIR_WS_INCLUDES . 'css/', '/^' . $page_base_name . '_/', '.php');
foreach ($directory_array as $key => $value) {
    echo "\n";
    require DIR_WS_INCLUDES . 'css/' . $value;
}

// -----
// Enable site-specific styling.
//
if (file_exists(DIR_WS_INCLUDES . 'css/site-specific-styles.php')) {
    echo "\n";
    require DIR_WS_INCLUDES . 'css/site-specific-styles.php';
}

// pull in any necessary JS for the page
require DIR_WS_INCLUDES . 'javascript_loader.php';
