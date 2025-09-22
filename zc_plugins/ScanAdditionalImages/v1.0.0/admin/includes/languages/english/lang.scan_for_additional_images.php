<?php

$define = [
    'HEADING_TITLE' => 'Scan for Additional Product Images to load into the Database',
    'TEXT_MAIN' => 'This tool will scan your images directory/subdirectories to assign additional images to a product.<br>It uses the main product image name (e.g. my-main-product-image.jpg) as a reference and assigns other images it finds with <a href="https://docs.zen-cart.com/user/images/image_filename_conventions/" target="_blank">matching names with suffixes</a> (e.g. my-main-product-image_anytext.jpg), to that product as additional images.<br>Database entries are created allowing all product image management to be done via the admin product edit page.<br>No images are modified nor uploaded.',
    'TEXT_STEP_1' => 'Step 1: Backup Your Data',
    'TEXT_STEP_1_DETAIL' => 'Before proceeding, ensure you have a complete backup of your database and images.',
    'TEXT_STEP_2' => 'Step 2: Start Scan',
    'TEXT_STEP_2_DETAIL' => 'Click the "Start Scan" button below to begin the scan process.',
    'TEXT_STEP_3' => 'Step 3: Completion',
    'TEXT_STEP_3_DETAIL' => 'Once the scan is complete, you will see a confirmation message in a blue status bar at the top of the page. Then verify that your product and category images are displaying correctly on the storefront.',
    'BUTTON_START_SCANNING' => 'Start Scan',
    'TEXT_SETTINGS' => 'Settings',
    'TEXT_TOGGLE_SECTION' => '(Toggle Section)',
    'TEXT_START_AT' => 'Start At',
    'TEXT_BATCH_SIZE' => 'Batch Size',
    'TEXT_SETTINGS_HELP' => 'Adjust only if needed. Values are read once when you press <strong>Start Scan</strong>.',
    'TEXT_PROGRESS' => 'Progress',
    'TEXT_TOTAL_PRODUCTS_WITH_IMAGES' => 'Total products with images',
    'TEXT_CUMULATIVE_PROCESSED' => 'Cumulative processed',
    'TEXT_CUMULATIVE_INSERTED' => 'Cumulative additional images inserted',
    'TEXT_PRODUCTS_REMAINING' => 'Products remaining to scan',
    'TEXT_THIS_BATCH_FOUND' => 'This Batch - Records Found',
    'TEXT_THIS_BATCH_INSERTED' => 'This Batch - Images Inserted',
    'TEXT_MESSAGE_LOG' => 'Message Log',
    'TEXT_RUNNING' => 'Running...',
    'TEXT_IDLE' => 'Idle',

    'TEXT_STARTED_WITH' => 'Started with start_at=',
    'TEXT_WITH_BATCH_SIZE' => 'batch_size=',
    'ERROR_EMPTY_RESPONSE' => 'Empty/invalid response (2xx). Aborting.',
    'TEXT_ERROR' => 'ERROR: ',
    'TEXT_SERVER_ENDED' => 'Server requested termination.',
    'TEXT_MISSING_RESPONSE' => 'Missing remaining in response. Aborting.',
    'TEXT_WARNING' => 'WARNING: ',

    'TEXT_STATUS_FOUND' => 'Batch done. Found: ',
    'TEXT_STATUS_PRODUCTS' => 'Products inspected: ',
    'TEXT_STATUS_IMAGES' => 'Images inserted: ',
    'TEXT_STATUS_REMAINING' => 'Remaining: ',

    'TEXT_COMPLETED' => 'Completed all work (remaining = 0).',
    'TEXT_NETWORK_ERROR' => 'Network error or request aborted. (HTTP Error)',
    'TEXT_HTTP_ERROR' => 'HTTP Error ',
    'TEXT_CANCELLED' => 'Cancelled by user.',

    'TEXT_NOTHING_TO_PROCESS' => 'No products (with images) to process with these batch selections.',
    'TEXT_QUERY_ONLY' => 'Query only - no processing performed.',
];

return $define;
