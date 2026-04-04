<?php
/**
 * Back-compat shim: App\Models\PluginControl is an alias of
 * Zencart\DbRepositories\PluginControlRepository.
 *
 * This file intentionally contains only a class_alias to avoid
 * duplicating the implementation for older encapsulated plugins
 * which still reference App\Models\PluginControl.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 27 New in v2.2.1 $
 */

namespace App\Models;

/**
 * Alias the repository implementation into the legacy App\Models namespace.
 *
 * @deprecated Use \Zencart\DbRepositories\PluginControlRepository instead.
 */
class_alias(\Zencart\DbRepositories\PluginControlRepository::class, __NAMESPACE__ . '\\PluginControl');
