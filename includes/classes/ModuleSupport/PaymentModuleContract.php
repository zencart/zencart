<?php
namespace Zencart\ModuleSupport;
interface PaymentModuleContract
{
    public function __construct();
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
    public function check(): bool;
    public function install();
    public function keys(): array;
    public function remove();
}
