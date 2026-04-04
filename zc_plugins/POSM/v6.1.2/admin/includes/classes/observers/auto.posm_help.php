<?php

class zcObserverPosmHelp extends base
{
    public function __construct()
    {
        $this->attach($this, ['NOTIFIER_PLUGIN_HELP_PAGE_URL_LOOKUP']);
    }

    protected function update(&$class, $eventID, $page, &$help_page)
    {
        if ($page === FILENAME_PRODUCTS_OPTIONS_STOCK) {
            $help_page = 'https://docs.zen-cart.com/user/admin_pages/catalog/options_stock_manager/'; 
        } elseif ($page === FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL) {
            $help_page = 'https://docs.zen-cart.com/user/admin_pages/catalog/options_stock_view_all/'; 
        } elseif ($page === FILENAME_POSM_FIND_DUPLICATE_MODELNUMS) {
            $help_page = 'https://docs.zen-cart.com/user/admin_pages/tools/posm_find_duplicate_models/'; 
        }
    }
}
