<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 18 Modified in v2.1.0-alpha1 $
 */

namespace Zencart\ViewBuilders;

use App\Models\PluginControl;
use Illuminate\Database\Eloquent\Builder;

class PluginManagerDataSource extends DataTableDataSource
{
    protected function buildInitialQuery(): Builder
    {
        return (new PluginControl())->query()->orderBy('name')->orderBy('unique_key');
    }
}
