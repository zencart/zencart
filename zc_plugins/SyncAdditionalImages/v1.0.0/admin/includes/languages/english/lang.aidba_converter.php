<?php

$define = [
    'HEADING_TITLE' => 'Load Additional Product Images to Database',
    'TEXT_MAIN' => 'This script will import your product images from the filesystem to track their names in the database.<br><br>Click the "Start Conversion" button below to begin the conversion process. Depending on the number of images, this may take some time. Please be patient and do not navigate away from this page until the process is complete. If the process is interrupted, simply start it again and it will resume from where it left off. <br><br><strong>Please re-run until it reports 0 images remaining to convert (in the blue status bar that will show at the top of the page).</strong><br><br>Once the conversion is complete, verify that your product and category images are displaying correctly on the storefront.<br><br>You can run this tool again anytime after uploading more images via FTP and it will attempt to sync them to products based on legacy filename matching patterns.<br><br>NOTE: This script is non-destructive and does not do anything to any actual image files; it only reads their names to match up with products.<br>This script also does NOT upload any actual image files.',
    'TEXT_STEP_1' => 'Step 1: Backup Your Data',
    'TEXT_STEP_1_DETAIL' => 'Before proceeding, ensure you have a complete backup of your database and images.',
    'TEXT_STEP_2' => 'Step 2: Start Conversion',
    'TEXT_STEP_2_DETAIL' => 'Click the "Start Conversion" button below to begin the conversion process.',
    'TEXT_STEP_3' => 'Step 3: Completion',
    'TEXT_STEP_3_DETAIL' => 'Once the conversion is complete, you will see a confirmation message in a blue status bar at the top of the page. Then verify that your product and category images are displaying correctly on the storefront.',
    'BUTTON_START_CONVERSION' => 'Start Conversion',

    'TEXT_ALL_CONVERTED' => 'All additional images have been converted. You can now remove this plugin using Plugin Manager if you wish.',
    'TEXT_PRODUCTS_PROCESSED' => ' products processed in this run.',
    'TEXT_CONVERSION_COMPLETED' => 'Additional images conversion completed. Please re-run this tool until it reports 0 images remaining to convert.',
];

return $define;
