<?php

$serverName = 'localhost:8000';
$serverSchema = 'http://';

$this->configParams = [
    'serverSchema' => $serverSchema,
    'base_url' =>  $serverSchema . $serverName . '/',
    'db_host' => 'localhost',
    'db_user' => 'travis',
    'db_name' => 'zencart',
    'db_password' => '',
    'db_prefix' => '',
    'installer_admin_name' => 'Admin',
    'store_owner_email' => 'test@example.com',
    'admin_user_main' => 'Admin',
    'admin_password_install' => 'developer1',
    'store_name' => 'Behat Test Store',
    'store_owner' => 'Behat Store Owner',
    'default_customer_email' => 'test1@example.com',
    'default_customer_password' => 'password',
    'uk_customer_email' => 'testuk@example.com',
    'uk_customer_password' => 'password',
    'canada_customer_email' => 'testcanada@example.com',
    'canada_customer_password' => 'password',
    'screenshot_path' => getcwd(),
    'take_screenshot_on_failed_step' => true,
    'dump_response_on_failed_step' => true,
    'die_after_dump_repsonse' => false,

];
