<?php

$define = [
    'HEADING_TITLE' => 'Scan for Additional Product Images to load into the Database',
    'TEXT_MAIN' => 'This tool will scan your images directory/subdirectories to assign additional images to a product.<br>It uses the main product image name (e.g. my-main-product-image.jpg) as a reference and assigns other images it finds with <a href="https://docs.zen-cart.com/user/images/image_filename_conventions/" target="_blank">matching names with _suffix</a> (e.g. my-main-product-image_anytext.jpg), to that product as additional images.<br>Database entries are created allowing all product image management to be done via the admin product edit page.<br>No images are modified nor uploaded.',
    'TEXT_STEP_1' => 'Step 1: Backup Your Data',
    'TEXT_STEP_1_DETAIL' => 'Before proceeding, ensure you have a complete backup of your database and images.',
    'TEXT_STEP_2' => 'Step 2: Start Scan',
    'TEXT_STEP_2_DETAIL' => 'Click the "Start Scan" button below to begin the scan process.',
    'TEXT_STEP_3' => 'Step 3: Completion',
    'TEXT_STEP_3_DETAIL' => 'Once the scan is complete, you will see a confirmation message in a blue status bar at the top of the page. Then verify that your product and category images are displaying correctly on the storefront.',
    'BUTTON_START_SCANNING' => 'Start Scan',

    'TEXT_ALL_SCANNED' => 'All additional images have been scanned and stored. You can now remove this plugin using Plugin Manager if you wish.',
    'TEXT_PRODUCTS_PROCESSED' => ' products processed in this run.',
    'TEXT_SCAN_COMPLETED' => 'Additional images scan completed. Please re-run this tool until it reports 0 images remaining to scan.',
];

return $define;
