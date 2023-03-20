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

    public function notify_build_keyword_search(&$class, $eventID, $unused, &$fields, &$string)
    {
        if (!empty($_REQUEST['restrictIDs']) && $_REQUEST['restrictIDs'] === 'on') {
            $removeElements = [
                'pd.products_name',
                'p.products_model',
                'pd.products_description',
                'cd.categories_name',
                'cd.categories_description',
            ];
            $fields = array_diff($fields, $removeElements);
        }
    }
}
