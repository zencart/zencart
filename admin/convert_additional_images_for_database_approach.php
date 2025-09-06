<?php
/**
 * Convert additional images from file-based approach to database approach
 *
 * Copyright 2003-2025 Zen Cart Development Team
 * copyright ZenExpert 2025
 * */

require('includes/application_top.php');

$action = $_POST['action'] ?? '';

if (!empty($action) && $action === 'convert') {
    $processed_file = 'processed_products_ids.txt';
    $processed_ids = file_exists($processed_file) ? array_map('intval', file($processed_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [];

    $counter = 0;

    $products_query = $db->Execute("SELECT products_id, products_image FROM " . TABLE_PRODUCTS);
    while (!$products_query->EOF) {
        $products_id = (int)$products_query->fields['products_id'];
        if (in_array($products_id, $processed_ids)) {
            $products_query->MoveNext();
            continue;
        }
        $products_image = $products_query->fields['products_image'];

        if (empty($products_image)) {
            $products_query->MoveNext();
            continue;
        }

        $image_extension = substr($products_image, strrpos($products_image, '.'));
        $image_base = str_replace($image_extension, '', $products_image);

        // If in subdirectory
        if (strrpos($products_image, '/')) {
            $image_match = substr($products_image, strrpos($products_image, '/') + 1);
            $image_match = str_replace($image_extension, '', $image_match) . '_';
            $image_base = $image_match;
        }

        // Use '_' suffix unless legacy mode
        if (defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy') {
            $image_base .= '_';
        }
        if (str_ends_with($image_base, '__')) {
            $image_base = substr($image_base, 0, -1);
        }

        $image_dir = DIR_FS_CATALOG_IMAGES;
        $matches = [];
        if ($dir = @dir($image_dir)) {
            while ($file = $dir->read()) {
                if (!is_dir($image_dir . $file) && substr($file, strrpos($file, '.')) === $image_extension) {
                    if (preg_match('/' . preg_quote($image_base, '/') . '/i', $file) === 1 && $file !== $products_image) {
                        $matches[] = $file;
                    }
                }
            }
            $dir->close();
        }

        // Insert matches into products_additional_images table
        foreach ($matches as $sort_order => $additional_image) {
            // Check if already exists
            $exists_query = $db->Execute(
                "SELECT id FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " WHERE products_id = " . $products_id . " AND additional_image = '" . zen_db_input($additional_image) . "'"
            );
            if ($exists_query->EOF) {
                $db->Execute(
                    "INSERT INTO " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (products_id, additional_image, sort_order) VALUES (" .
                    $products_id . ", '" . zen_db_input($additional_image) . "', " . (int)$sort_order . ")"
                );
            }
        }

        file_put_contents($processed_file, $products_id . PHP_EOL, FILE_APPEND);

        $counter++;

        $products_query->MoveNext();
    }
    if($counter === 0) {
        if (file_exists($processed_file)) {
            @unlink($processed_file);
        }
        $messageStack->add_session(TEXT_ALL_CONVERTED, 'info');
        $db->Execute("UPDATE " . TABLE_ADMIN_PAGES . " SET display_on_menu = 'N' WHERE page_key = 'toolsAidba'");

    } else {
        $messageStack->add_session($counter . TEXT_PRODUCTS_PROCESSED, 'success');
        $messageStack->add_session(TEXT_CONVERSION_COMPLETED, 'success');
    }
    zen_redirect(zen_href_link(FILENAME_AIDBA));
}

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<div class="container-fluid">
    <!-- body_text //-->

    <h1><?php echo HEADING_TITLE; ?></h1>
    <?php echo TEXT_MAIN; ?>
    <br><br>
    <h3><?php echo TEXT_STEP_1; ?></h3>
    <p><?php echo TEXT_STEP_1_DETAIL; ?></p>
    <h3><?php echo TEXT_STEP_2; ?></h3>
    <p><?php echo TEXT_STEP_2_DETAIL; ?></p>
    <h3><?php echo TEXT_STEP_3; ?></h3>
    <p><?php echo TEXT_STEP_3_DETAIL; ?></p>
    <?php
    echo zen_draw_form('convert_images_to_db', FILENAME_AIDBA, '', 'post', 'class="form-horizontal"');
    echo zen_draw_hidden_field('action', 'convert');
    ?>

    <div class="buttonRow">
        <button type="submit" class="btn btn-primary"><?php echo BUTTON_START_CONVERSION; ?></button>
    </div>

    <?php echo '</form>'; ?>

    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
