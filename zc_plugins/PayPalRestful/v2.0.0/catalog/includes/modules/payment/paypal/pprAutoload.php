<?php
/**
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 *
 *  Last updated: v2.0.0
 *
 */
$pprautoload_dir = __DIR__ . '/';

global $psr4Autoloader;

$psr4Autoloader->addPrefix('PayPalRestful\Admin', $pprautoload_dir . 'PayPalRestful/Admin');
$psr4Autoloader->addPrefix('PayPalRestful\Admin\Formatters', $pprautoload_dir . 'PayPalRestful/Admin/Formatters');
$psr4Autoloader->addPrefix('PayPalRestful\Api', $pprautoload_dir . 'PayPalRestful/Api');
$psr4Autoloader->addPrefix('PayPalRestful\Api\Data', $pprautoload_dir . 'PayPalRestful/Api/Data');
$psr4Autoloader->addPrefix('PayPalRestful\Common', $pprautoload_dir . 'PayPalRestful/Common');
$psr4Autoloader->addPrefix('PayPalRestful\Token', $pprautoload_dir . 'PayPalRestful/Token');
$psr4Autoloader->addPrefix('PayPalRestful\Webhooks', $pprautoload_dir . 'PayPalRestful/Webhooks');
$psr4Autoloader->addPrefix('PayPalRestful\Zc2Pp', $pprautoload_dir . 'PayPalRestful/Zc2Pp');
