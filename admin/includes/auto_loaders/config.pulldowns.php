<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */

    $autoLoadConfig[210][] = [
        'autoType' => 'class',
        'loadFile' => 'pulldown.php',
        'classPath' => DIR_FS_CATALOG . DIR_WS_CLASSES,
    ];

    $autoLoadConfig[210][] = [
        'autoType' => 'class',
        'loadFile' => 'productPulldown.php',
        'classPath' => DIR_FS_CATALOG . DIR_WS_CLASSES,
    ];

    $autoLoadConfig[210][] = [
        'autoType' => 'class',
        'loadFile' => 'categoryPulldown.php',
        'classPath' => DIR_FS_CATALOG . DIR_WS_CLASSES,
    ];

    $autoLoadConfig[210][] = [
        'autoType' => 'class',
        'loadFile' => 'productOptionsPulldown.php',
        'classPath' => DIR_FS_CATALOG . DIR_WS_CLASSES,
    ];
