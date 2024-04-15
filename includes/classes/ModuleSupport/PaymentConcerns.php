<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

use Zencart\Traits\ObserverManager;

trait PaymentConcerns
{
    use ModuleConcerns;

    public function javascript_validation(): string
    {
        return false;
    }

    public function selection(): array
    {
        return [
            'id' => $this->code,
            'module' => $this->title
        ];
    }

    public function pre_confirmation_check()
    {
        return false;

    }

    public function confirmation()
    {
        return [
            'title' => $this->title
        ];
    }

    public function process_button()
    {
        return false;
    }
    public function clear_payments()
    {

    }

    public function before_process()
    {
        return false;
    }

    public function after_order_create($orders_id)
    {

    }

    public function after_process()
    {
        return false;
    }
    public function admin_notification($zf_order_id)
    {

    }

}
