<?php

$define = [
    'HEADING_TITLE' => 'Scan for Additional Product Images to load into the Database',
    'TEXT_MAIN' => 'The way Zen Cart handles "additional" product images is to have one "main" image assigned to the product and then separately assign additional images to show alongside.<br>
You have two ways to assign additional images to a product:<br>
1) Directly in your Admin product-edit page, one product at a time, where you can upload (and delete) additional images for a product, without caring about the filename or renaming image files to suit a pattern. This requires that your <strong>Admin-&gt;Configuration-&gt;Images-&gt;Additional Images filename matching pattern</strong> setting is set to <strong>Database</strong>.<br>
2) By FTP, by naming all the additional image filenames according to a prescribed <a href="https://docs.zen-cart.com/user/images/image_filename_conventions/" target="_blank">naming convention</a> and letting those patterns automatically choose the additional images to display for that product on the storefront.<br><br>
This tool is designed to switch from method 2 to method 1 by scanning your images directory/subdirectories for images you have already uploaded, and inserting them into the database and assigning them to the corresponding matching products.<br>
It uses the main product image name (e.g. my-main-product-image.jpg) as a reference and assigns other images it finds with <a href="https://docs.zen-cart.com/user/images/image_filename_conventions/" target="_blank">matching names with suffixes</a> (e.g. my-main-product-image_anytext.jpg), to that product as additional images.<br>
Database entries are created allowing all product image management to be done via the admin product edit page.<br>No images are modified nor uploaded.',
    'TEXT_TIP_1' => 'Tip: You can run this tool multiple times. It will skip duplicate product-image assignments, so it is safe to re-run it if you add more images later, such as after a vendor provides updates ... but remember, the tool only picks up images whose filenames match the naming conventions (see links above).',
    'TEXT_STEP_1' => 'Step 1: Backup Your Data',
    'TEXT_STEP_1_DETAIL' => 'Before proceeding, ensure you have a complete backup of your database and images. This tool is not destructive, but now is a wise time to make sure you have a backup of everything.',
    'TEXT_STEP_2' => 'Step 2: Start Scan',
    'TEXT_STEP_2_DETAIL' => 'Click the "Start Scan" button below to begin the scan process.',
    'TEXT_STEP_3' => 'Step 3: Completion',
    'TEXT_STEP_3_DETAIL' => 'You will see in the Message Log area when the process is completed. Then go verify that your product and category images are displaying correctly on the storefront.<br><br>
Remember, to use these database-tracked images, your <strong>Admin-&gt;Configuration-&gt;Images-&gt;Additional Images filename matching pattern</strong> setting must be set to <strong>Database</strong>, so if this is your first time running this tool, be sure to go change that setting, else you will not be able to manage Additional Images on the Admin product-edit page.',
    'BUTTON_START_SCANNING' => 'Start Scan',
    'TEXT_SETTINGS' => 'Settings',
    'TEXT_TOGGLE_SECTION' => '(Toggle Section)',
    'TEXT_START_AT' => 'Start At (default 0)',
    'TEXT_BATCH_SIZE' => 'Batch Size (default 10, max 50)',
    'TEXT_SETTINGS_HELP' => 'Adjust only if needed. Values are read once when you press <strong>Start Scan</strong>. Smaller batch sizes are more efficient and avoid timeouts.',
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
    'TEXT_NETWORK_ERROR' => 'Network error, timeout, or request aborted. (HTTP Error). Try smaller batch sizes.',
    'TEXT_HTTP_ERROR' => 'HTTP Error ',
    'TEXT_CANCELLED' => 'Cancelled by user.',

    'TEXT_NOTHING_TO_PROCESS' => 'No products (with images) to process with these batch selections.',
    'TEXT_QUERY_ONLY' => 'Query only - no processing performed.',
];

return $define;
