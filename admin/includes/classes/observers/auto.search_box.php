<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 13 Modified in v2.1.0 $
 */

class zcObserverSearchBox extends base
{
    public function __construct()
    {
        $this->attach(
            $this,
            [
                'NOTIFY_BUILD_KEYWORD_SEARCH',
            ]
        );
    }

    public function update(&$class, $eventID, &$p1, &$p2, &$p3, &$p4)
    {
        switch ($eventID) {
            case 'NOTIFY_BUILD_KEYWORD_SEARCH':
                if (!empty($_REQUEST['restrictIDs']) && $_REQUEST['restrictIDs'] === 'on') {
                    $removeElements = [
                        'pd.products_name',
                        'p.products_model',
                        'p.products_mpn',
                        'pd.products_description',
                        'cd.categories_name',
                        'cd.categories_description',
                    ];
                    $p2 = array_diff($p2, $removeElements);
                }
                break;
            default:
                break;
        }
    }
}
