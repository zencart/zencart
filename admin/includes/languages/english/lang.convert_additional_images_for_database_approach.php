<?php

$define = [
    'HEADING_TITLE' => 'Convert Images to Database Approach',
    'TEXT_MAIN' => 'This script will convert your product images from the filesystem to the database approach. It will move all images from the filesystem into the database and update your product records to point to the database images. <br><br>Before running this script, please ensure you have a complete backup of your database and images. This process is irreversible. <br><br>Click the "Start Conversion" button below to begin the conversion process. Depending on the number of images, this may take some time. Please be patient and do not navigate away from this page until the process is complete. If the process is interrupted, simply start it again and it will resume from where it left off. <br><br><strong>Please re-run until it reports 0 images remaining to convert.</strong><br><br>Once the conversion is complete, verify that your product and category images are displaying correctly on the frontend. If you encounter any issues, you can restore your database and images from the backup you created earlier.',
    'TEXT_STEP_1' => 'Step 1: Backup Your Data',
    'TEXT_STEP_1_DETAIL' => 'Before proceeding, ensure you have a complete backup of your database and images.',
    'TEXT_STEP_2' => 'Step 2: Start Conversion',
    'TEXT_STEP_2_DETAIL' => 'Click the "Start Conversion" button below to begin the conversion process.',
    'TEXT_STEP_3' => 'Step 3: Completion',
    'TEXT_STEP_3_DETAIL' => 'Once the conversion is complete, you will see a confirmation message. Verify that your product and category images are displaying correctly on the frontend.',
    'BUTTON_START_CONVERSION' => 'Start Conversion',
];

return $define;
