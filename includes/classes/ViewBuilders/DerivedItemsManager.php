<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jul 19 New in v1.5.8-alpha $
 */

namespace Zencart\ViewBuilders;

use Zencart\FileSystem\FileSystem;
use Illuminate\Database\Eloquent\Model;

class DerivedItemsManager
{
    public function process(Model $tableRow, string $colName, array $columnInfo) : string
    {
        if (!isset($columnInfo['derivedItem'])) {
            return $tableRow[$colName];
        }
        $colData = $this->processDerivedItem($tableRow, $colName, $columnInfo);
        return $colData;
    }

    protected function processDerivedItem(Model $tableRow, string $colName, array $columnInfo) : string
    {
        $type = $columnInfo['derivedItem']['type'];
        switch ($type) {
            case 'local':
                $result = $this->{$columnInfo['derivedItem']['method']}($tableRow, $colName, $columnInfo);
                return $result;
                break;
            case 'closure':
                $result = $columnInfo['derivedItem']['method']($tableRow, $colName, $columnInfo);
                return $result;
                break;
        }
    }

    protected function booleanReplace(Model $tableRow, string $colName, array $columnInfo) : string
    {
        $params = $columnInfo['derivedItem']['params'];
        $listValue = $tableRow[$colName];
        $result = $params['false'];
        if ($listValue) $result = $params['true'];
        return $result;
    }

    protected function arrayReplace(Model $tableRow, string $colName, array $columnInfo) : string
    {
        $params = $columnInfo['derivedItem']['params'];
        $listValue = $tableRow[$colName];
        $result = $params[$listValue];
        return $result;
    }

    protected function getPluginFileSize(Model $tableRow, string $colName, array $columnInfo) : string
    {
        $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $tableRow['unique_key'] . '/';
        $fs = new FileSystem;
        $dirSize = $fs->getDirectorySize($filePath);
        return $dirSize;
    }

    protected function getLanguageTranslationForName(Model $tableRow, string $colName, array $columnInfo) : string
    {
        return zen_lookup_admin_menu_language_override('plugin_name', $tableRow['unique_key'], $tableRow['name']);
    }
}
