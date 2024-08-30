<?php
/**
 * version_upgrades.php
 *
 * Specifies the list of versions which zc_install can upgrade from/to
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

// Upgrader format: [target version => ['required' => pre-existing version]]
return [
    '1.2.6' => ['required' => false],
    '1.2.7' => ['required' => '1.2.6'],
    '1.3.0' => ['required' => '1.2.7'],
    '1.3.5' => ['required' => '1.3.0'],
    '1.3.6' => ['required' => '1.3.5'],
    '1.3.7' => ['required' => '1.3.6'],
    '1.3.8' => ['required' => '1.3.7'],
    '1.3.9' => ['required' => '1.3.8'],
    '1.5.0' => ['required' => '1.3.9'],
    '1.5.1' => ['required' => '1.5.0'],
    '1.5.2' => ['required' => '1.5.1'],
    '1.5.3' => ['required' => '1.5.2'],
    '1.5.4' => ['required' => '1.5.3'],
    '1.5.5' => ['required' => '1.5.4'],
    '1.5.6' => ['required' => '1.5.5'],
    '1.5.7' => ['required' => '1.5.6'],
    '1.5.8' => ['required' => '1.5.7'],
    '2.0.0' => ['required' => '1.5.8'],
    '2.1.0' => ['required' => '2.0.0'],
];

