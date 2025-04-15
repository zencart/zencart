<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

trait OrderTotalCCConcerns
{
    use OrderTotalConcerns;

    public function credit_selection(): false|array
    {
        return false;
    }
    public function update_credit_account($i): void
    {}
    public function collect_posts(): void
    {}
    public function pre_confirmation_check($returnOrderTotalOnly = false): void
    {}
    public function apply_credit(): void
    {}
    public function clear_posts(): void
    {}
}
