<?php
/**
 * Scan additional image names from filesystem to database table
 *
 * Copyright 2003-2025 Zen Cart Development Team
 * copyright ZenExpert 2025
 */

require 'includes/application_top.php';

$action = $_POST['action'] ?? '';

if (!empty($action) && $action === 'scan') {
    zen_set_time_limit(600); // Extend time limit to 10 minutes

    $counter = $inserted = 0;

    $products_query = $db->Execute(
        "SELECT products_id, products_image
        FROM " . TABLE_PRODUCTS . "
        WHERE products_image IS NOT NULL
        AND products_image != '" . zen_db_input(PRODUCTS_IMAGE_NO_IMAGE) . "'"
    );

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
        $image_base = basename($products_image, $image_extension);

        // Use '_' suffix unless legacy mode
        if (defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy') {
            $image_base .= '_';
        }
        if (str_ends_with($image_base, '__')) {
            $image_base = substr($image_base, 0, -1);
        }

        $matches = [];
        // Scan directory for matching files using glob iterator, which sorts alphabetically (so sort_order is retained)
        $images = zen_get_files_in_directory($image_dir, $image_extension);
        foreach ($images as $file) {
            $file = preg_replace('/^' . preg_quote($image_dir, '/') . '/i', '', $file);
            if (!is_dir($image_dir . $file)) {
                if (preg_match('/' . preg_quote($image_base, '/') . '/i', $file) === 1 && $file !== $products_image) {
                    $matches[] = $file;
                }
            }
        }

        // This loop performs many filesystem stat operations, which may overload PHP's cache. So we clean it up here.
        // https://www.php.net/manual/en/function.clearstatcache.php
        clearstatcache();

        // Insert matches into products_additional_images table
        foreach ($matches as $sort_order => $additional_image) {
            // insert if new, ignoring if duplicate (unique index is set on products_id + additional_image)
            $result = $db->Execute(
                "INSERT IGNORE INTO " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (products_id, additional_image, sort_order)
                VALUES (" . $products_id . ", '" . zen_db_input($subdir . $additional_image) . "', " . (int)$sort_order . ")"
            );
            $inserted += mysqli_affected_rows($result->link);
        }

        $counter++;
    }
    if ($inserted === 0) {
        $messageStack->add_session(TEXT_ALL_SCANNED, 'info');
    } else {
        $messageStack->add_session($counter . TEXT_PRODUCTS_PROCESSED, 'success');
        $messageStack->add_session(TEXT_SCAN_COMPLETED, 'success');
    }
    zen_redirect(zen_href_link(FILENAME_SCAN_FOR_ADDITIONAL_IMAGES));
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
    echo zen_draw_form('scan_images_to_db', FILENAME_SCAN_FOR_ADDITIONAL_IMAGES, '', 'post', 'class="form-horizontal"');
    echo zen_draw_hidden_field('action', 'scan');
    ?>

    <div class="buttonRow">
        <button type="submit" class="btn btn-primary"><?= BUTTON_START_SCANNING ?></button>
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
