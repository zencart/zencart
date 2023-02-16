<?php


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
