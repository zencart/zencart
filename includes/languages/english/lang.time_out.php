<?php
$define = [
    'NAVBAR_TITLE' => 'Login Time Out',
    'HEADING_TITLE' => 'Whoops! Your session has expired.',
    'HEADING_TITLE_LOGGED_IN' => 'Whoops! Sorry, but you are not allowed to perform the action requested. ',
    'TEXT_INFORMATION' => '<p>If you were placing an order, please login and your shopping cart will be restored. You may then go back to the checkout and complete your final purchases.</p><p>If you had completed an order and wish to review it' . (DOWNLOAD_ENABLED == 'true' ? ', or had a download and wish to retrieve it' : '') . ', please go to your <a href="' . zen_href_link(FILENAME_ACCOUNT) . '">My Account</a> page to view your order.</p>',
    'TEXT_INFORMATION_LOGGED_IN' => 'You are still logged in to your account and may continue shopping. Please choose a destination from a menu.',
    'HEADING_RETURNING_CUSTOMER' => 'Login',
];

return $define;
