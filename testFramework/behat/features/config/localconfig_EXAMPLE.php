<?php

$serverName = 'localhost';
$serverSchema = 'https://';

$this->configParams = [
    'serverSchema' => $serverSchema,
    'base_url' =>  $serverSchema . $serverName . '/',
    'db_host' => 'localhost',
    'db_user' => 'travis',
    'db_name' => 'zencart',
    'db_password' => '',
    'db_prefix' => '',
    'installer_admin_name' => 'Admin',
    'store_owner_email' => 'test@' . $serverName,
    'admin_user_main' => 'Admin',
    'admin_password_install' => 'developer1',
    'store_name' => 'Behat Test Store',
    'store_owner' => 'Behat Store Owner',
    'default_customer_email' => 'test1@' . $serverName,
    'default_customer_password' => 'password',
    'uk_customer_email' => 'testuk@' . $serverName,
    'uk_customer_password' => 'password',
    'canada_customer_email' => 'testcanada@' . $serverName,
    'canada_customer_password' => 'password',
    'screenshot_path' => getcwd(),
    'take_screenshot_on_failed_step' => true,
    'dump_response_on_failed_step' => true,
    'die_after_dump_repsonse' => false,

];
