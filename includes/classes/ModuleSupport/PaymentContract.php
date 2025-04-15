<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;
interface PaymentContract extends ModuleContract
{
    public function update_status();
    public function javascript_validation(): string;
    public function selection(): array;
    public function pre_confirmation_check();
    public function confirmation();
    public function process_button();
    public function clear_payments();
    public function before_process();
    public function after_order_create($orderId);
    public function after_process();
    public function admin_notification($orderId);
}
