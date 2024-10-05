<?php
/**
 * COD Payment Module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
 */

use Zencart\ModuleSupport\PaymentBase;
use Zencart\ModuleSupport\PaymentContract;
use Zencart\ModuleSupport\PaymentConcerns;

class cod  extends PaymentBase implements PaymentContract
{
    use PaymentConcerns;

    public string $version = '1.0.0';
    public string $code = 'cod';
    public string $defineName = 'COD';
    protected function addCustomConfigurationKeys(): array
    {
        $configKeys = [];
        return $configKeys;
    }
}
