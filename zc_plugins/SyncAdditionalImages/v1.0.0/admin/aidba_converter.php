<?php
/**
 * Convert additional images from file-based approach to database approach
 *
 * Copyright 2003-2025 Zen Cart Development Team
 * copyright ZenExpert 2025
 */

require 'includes/application_top.php';

$action = $_POST['action'] ?? '';

if (!empty($action) && $action === 'convert') {
    $counter = $inserted = 0;

    $products_query = $db->Execute("SELECT products_id, products_image FROM " . TABLE_PRODUCTS . " WHERE products_image IS NOT NULL");
    foreach ($products_query as $product) {
        $products_id = (int)$product['products_id'];
        $products_image = $product['products_image'];

        $image_extension = substr($products_image, strrpos($products_image, '.'));
        $image_base = str_replace($image_extension, '', $products_image);

        // Detect subdirectory
        $subdir = '';
        if (strpos($products_image, '/') !== false) {
            $subdir = substr($products_image, 0, strrpos($products_image, '/') + 1);
        }
        $image_dir = DIR_FS_CATALOG_IMAGES . $subdir;

        // Get base filename without extension
        $image_filename = basename($products_image, $image_extension);
        $image_base = $image_filename;

        // Use '_' suffix unless legacy mode
        if (defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy') {
            $image_base .= '_';
        }
        if (str_ends_with($image_base, '__')) {
            $image_base = substr($image_base, 0, -1);
        }

        $matches = [];
        // Scan directory for matching files using glob, which sorts alphabetically
        $images = zen_get_files_in_directory($image_dir, $image_extension);
        foreach ($images as $file) {
            $file = preg_replace('/^' . preg_quote($image_dir, '/') . '/i', '', $file);
            if (!is_dir($image_dir . $file)) {
                if (preg_match('/' . preg_quote($image_base, '/') . '/i', $file) === 1 && $file !== $products_image) {
                    $matches[] = $file;
                }
            }
        }

        // Insert matches into products_additional_images table
        foreach ($matches as $sort_order => $additional_image) {
            // Check if already exists
            $exists_query = $db->Execute(
                "SELECT id FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " WHERE products_id = " . $products_id . " AND additional_image = '" . zen_db_input($subdir . $additional_image) . "'"
            );
            if ($exists_query->EOF) {
                $db->Execute(
                    "INSERT INTO " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (products_id, additional_image, sort_order)
                    VALUES (" . $products_id . ", '" . zen_db_input($subdir . $additional_image) . "', " . (int)$sort_order . ")"
                );
                $inserted++;
            }
        }

        $counter++;
    }
    if ($inserted === 0) {
        $messageStack->add_session(TEXT_ALL_CONVERTED, 'info');
        //$db->Execute("UPDATE " . TABLE_ADMIN_PAGES . " SET display_on_menu = 'N' WHERE page_key = 'toolsAidba'");
    } else {
        $messageStack->add_session($counter . TEXT_PRODUCTS_PROCESSED, 'success');
        $messageStack->add_session(TEXT_CONVERSION_COMPLETED, 'success');
    }
    zen_redirect(zen_href_link(FILENAME_AIDBA));
}

?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->

<div class="container-fluid">
    <!-- body_text //-->

    <h1><?= HEADING_TITLE ?></h1>
    <?= TEXT_MAIN ?><br>
    <br>
    <h3><?= TEXT_STEP_1 ?></h3>
    <p><?= TEXT_STEP_1_DETAIL ?></p>
    <h3><?= TEXT_STEP_2 ?></h3>
    <p><?= TEXT_STEP_2_DETAIL ?></p>
    <h3><?= TEXT_STEP_3 ?></h3>
    <p><?= TEXT_STEP_3_DETAIL ?></p>
    <?php
    echo zen_draw_form('convert_images_to_db', FILENAME_AIDBA, '', 'post', 'class="form-horizontal"');
    echo zen_draw_hidden_field('action', 'convert');
    ?>

    <div class="buttonRow">
        <button type="submit" class="btn btn-primary"><?= BUTTON_START_CONVERSION ?></button>
    </div>

    <?= '</form>' ?>

    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
