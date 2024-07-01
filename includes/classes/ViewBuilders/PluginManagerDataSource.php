<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jul 19 New in v1.5.8-alpha $
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
