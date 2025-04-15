<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

interface ModuleContract
{
    public function __construct();
    public function check(): bool;
    public function install();
    public function keys(): array;
    public function remove();

}
