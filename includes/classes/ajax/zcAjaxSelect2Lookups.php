<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: zcAjaxSelect2Lookups 2025-10 DrByte $
 * @since ZC v2.2.0
 */

class zcAjaxSelect2Lookups extends base
{
    /**
     * @since ZC v2.2.0
     */
    public function getProductsForSpecials(): bool|array
    {
        if (!isset($_POST['q'])) {
            return false;
        }

        $results = $this->lookup_products_select2($_POST['q'], ['specials']);

        return [
            'results' => $results,
            'pagination' => [
                'more' => false,
            ],
        ];
    }

    /**
     * @since ZC v2.2.0
     */
    protected function lookup_products_select2(string $lookup = '', array $exclusion_formulas = [], array $excluded_products = [], bool $show_id = true, bool $show_model = true): array
    {
        global $currencies, $db;

        if (!is_object($currencies) && !class_exists('currencies')) {
            require DIR_WS_CLASSES . 'currencies.php';
            $currencies = new currencies();
        }

        $query = "SELECT p.products_id, pd.products_name, p.products_price, p.products_model
            FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
            WHERE p.products_id = pd.products_id
            AND pd.language_id = :languageID";

        $order_by = " ORDER BY products_name";

        $exclude_specials = '';
        if (in_array('specials', $exclusion_formulas, true)) {
            $exclude_specials = " AND p.products_id NOT IN (SELECT DISTINCT products_id FROM " . TABLE_SPECIALS . ") ";
        }

        $exclude_featured_products = '';
        if (in_array('featured', $exclusion_formulas, true)) {
            $exclude_featured_products = " AND p.products_id NOT IN (SELECT DISTINCT products_id FROM " . TABLE_FEATURED . ") ";
        }

        $exclude_gv = '';
        // exclude GV's if feature is disabled
        if (!defined('MODULE_ORDER_TOTAL_GV_SPECIAL') || MODULE_ORDER_TOTAL_GV_SPECIAL === 'false') {
            $exclude_gv = " AND p.products_model NOT LIKE 'GIFT%' ";
        }

// @TODO:optionally offer exclusion of products that cannot be added to cart:
//        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
//        WHERE pt.allow_add_to_cart = 'N'

        $search_query = '';
        if ($lookup !== '') {
            $q = $db->prepare_input($lookup);
            $search_query = "  AND (pd.products_name LIKE '%$q%' OR p.products_model LIKE '%$q%' OR p.products_id LIKE '%$q%' OR p.products_price LIKE '%$q%' ) ";
        }

        $query = $db->bindVars($query, ':languageID', (int)$_SESSION['languages_id'], 'integer');
        $results = $db->Execute($query . $exclude_specials . $exclude_featured_products . $exclude_gv . $search_query . $order_by);

        $records = [];
        foreach ($results as $result) {
            if (in_array($result['products_id'], $excluded_products, false)) {
                continue;
            }

            $display_price = zen_get_products_base_price($result['products_id']);

            $records[] = [
                'id' => (string)$result['products_id'],
                'text' => $result['products_name'] .
                    ' (' . $currencies->format($display_price) . ')' .
                    ($show_model ? ' [' . $result['products_model'] . '] ' : '') .
                    ($show_id ? ' - ID# ' . $result['products_id'] : '')
                ,
            ];
        }

        return $records;
    }
}
