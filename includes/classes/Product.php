<?php
declare(strict_types=1);

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 08 Modified in v2.1.0-beta1 $
 *
 * @var language $lng
 * @var queryFactory $db
 */

use Zencart\Traits\NotifierManager;

class Product
{
    use NotifierManager;

    protected array $data;
    protected array $languages;

    /** @deprecated use ->get('property') or ->getData()  */
    public array $fields;

    /** @deprecated use !exists()  */
    public bool $EOF = true;

    public function __construct(protected ?int $product_id = null)
    {
        $this->initLanguages();

        if ($this->product_id !== null) {
            $this->data = $this->loadProductDetails($this->product_id);

            // set some backward compatibility properties
            $this->fields = $this->data;
            $this->EOF = empty($this->data);
        }
    }

    public function forLanguage(?int $language_id): self
    {
        $this->data = $this->getDataForLanguage($language_id);
        $this->fields = $this->data;

        return $this;
    }

    public function withDefaultLanguage(): self
    {
        $this->data = $this->getDataForLanguage();
        $this->fields = $this->data;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function get(string $name)
    {
        return $this->data[$name] ?? $this->data['lang'][$this->languages[(int)$_SESSION['languages_id']]][$name] ?? null;
    }

    /**
     * Same as getData(), but for specific language only
     */
    public function getDataForLanguage(?int $language_id = null): ?array
    {
        if (empty($language_id)) { // empty allows for 0 which might occur if null is pre-casted to int before passing to this function
            $language_id = (int)$_SESSION['languages_id'];
        }
        $data = $this->data;

        // -----
        // If this request is for a product being created, it might not yet have
        // its language elements (e.g. products_name) stored.  In this case, simply
        // return the product's base information.
        //
        if (!isset($data['lang'])) {
            return $data;
        }

        // strip all languages except specified one, and merge into parent array instead of sub-array
        foreach ($data['lang'][$this->languages[$language_id]] as $key => $value) {
            $data[$key] = $value;
        }
        unset($data['lang']);

        return $data;
    }

    public function getId(): ?int
    {
        return $this->product_id;
    }

    public function exists(): bool
    {
        return !empty($this->product_id) && !empty($this->data);
    }
    public function isValid(): bool
    {
        return !empty($this->data);
    }

    public function isLinked(): bool
    {
        return ($this->data['linked_categories_count'] ?? 0) > 0;
    }

    public function isVirtual(): bool
    {
        return ($this->data['products_virtual'] ?? 0) === '1';
    }

    public function isAlwaysFreeShipping(): bool
    {
        return ($this->data['product_is_always_free_shipping'] ?? '') === '1';
    }

    public function status(): int
    {
        return (int)($this->data['products_status'] ?? 0);
    }

    public function isGiftVoucher(): bool
    {
        return str_starts_with($this->data['products_model'] ?? '', 'GIFT');
    }

    public function allowsAddToCart(): bool
    {
        if (empty($this->data)) {
            return false;
        }

        $allow_add_to_cart = ($this->data['allow_add_to_cart'] ?? 'N') !== 'N';

        if ($allow_add_to_cart && $this->isGiftVoucher()) {
            // if GV feature disabled, can't allow GV's to be added to cart
            if (!defined('MODULE_ORDER_TOTAL_GV_STATUS') || MODULE_ORDER_TOTAL_GV_STATUS !== 'true') {
                $allow_add_to_cart = false;
            }
        }

        $this->notify('NOTIFY_GET_PRODUCT_ALLOW_ADD_TO_CART', $this->product_id, $allow_add_to_cart, $this->data);

        // test for boolean and for 'Y', since observer might try to return 'Y'
        return in_array($allow_add_to_cart, [true, 'Y'], true);
    }

    public function getProductQuantity(): int|float
    {
        $quantity = $this->data['products_quantity'] ?? '0';
        $this->notify('NOTIFY_GET_PRODUCT_QUANTITY', $this->product_id, $quantity);
        return zen_str_to_numeric((string)$quantity);
    }

    public function getTypeHandler(): string
    {
        return ($this->data['type_handler'] ?? 'product');
    }

    public function getInfoPage(): string
    {
        return $this->getTypeHandler() . '_info';
    }

    public function hasPriceQuantityDiscounts(): bool
    {
        if (empty($this->data)) {
            return false;
        }

        global $db;
        $sql = "SELECT products_id FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id=" . (int)$this->product_id;
        $results = $db->Execute($sql, 1);
        return !$results->EOF;
    }

    public function hasPriceSpecials()
    {
        if (empty($this->data)) {
            return false;
        }

        global $db;
        $sql = "SELECT products_id FROM " . TABLE_SPECIALS . " WHERE products_id=" . (int)$this->product_id;
        $results = $db->Execute($sql, 1);
        return !$results->EOF;
    }

    public function priceIsByAttribute(): bool
    {
        return ($this->data['products_priced_by_attribute'] ?? '0') === '1';
    }

    public function priceIsFree(): bool
    {
        return ($this->data['product_is_free'] ?? '0') === '1';
    }

    public function priceIsCall(): bool
    {
        return ($this->data['product_is_call'] ?? '0') === '1';
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    protected function loadProductDetails(int $product_id, ?int $language_id = null): array
    {
        global $db;

        $sql = "SELECT p.*, pt.allow_add_to_cart, pt.type_handler, m.manufacturers_name, m.manufacturers_image
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON (p.products_type = pt.type_id)
                LEFT JOIN " . TABLE_MANUFACTURERS . " m USING (manufacturers_id)
                WHERE p.products_id = " . (int)$product_id;
        $product = $db->Execute($sql, 1, true, 900);

        if ($product->EOF) {
            $data_override = [];
            $this->notify('NOTIFY_GET_PRODUCT_OBJECT_DETAILS_NOT_FOUND', ['product_id' => $product_id, 'language_id' => $language_id], $data_override);
            return $data_override;
        }

        $data = $product->fields;
        $data['id'] = $data['products_id'];
        $data['product_id'] = $data['products_id'];
        $data['info_page'] = $data['type_handler'] . '_info';
        //$data['parent_category_id'] = $data['master_categories_id'];

        /**
         * Add $data['lang'][code] = [products_name, products_description, etc] for each language
         */
        $sql = "SELECT pd.*
                FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE pd.products_id = " . (int)$product_id . "
                ORDER BY language_id";
        $pd = $db->Execute($sql, null, true, 900);
        foreach ($pd as $result) {
            unset($result['products_id']);
            $data['lang'][$this->languages[$result['language_id']]] = $result;
        }

        // count linked categories
        $sql = "SELECT categories_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc WHERE products_id=" . (int)$product_id;
        $results = $db->Execute($sql, null, true, 900);
        $data['linked_categories_count'] = $results->RecordCount();
        $data['linked_categories'] = [];
        foreach ($results as $result) {
            if ($result['categories_id'] === $data['master_categories_id']) {
                $data['linked_categories_count']--;
                continue;
            }
            $data['linked_categories'][] = $result['categories_id'];
        }

        // get cPath
        $categories = [];
        zen_get_parent_categories($categories, $data['master_categories_id']);
        $categories = array_reverse($categories);
        $categories[] = $data['master_categories_id'];
        $data['cPath'] = implode('_', $categories);


        //Allow an observer to modify details
        $this->notify('NOTIFY_GET_PRODUCT_OBJECT_DETAILS', $product_id, $data);

        return $data;
    }

    protected function initLanguages(): void
    {
        global $lng;

        if ($lng === null) {
            $lng = new language();
        }

        $this->languages = $lng->get_language_list();  // [1 => 'en', 2 => 'fr']
    }
}


/* This class essentially deprecates the following functions (note Notifier hook differences):
zen_get_product_details (er, well, it's now a helper to access this class)
zen_get_products_category_id
zen_products_id_valid
zen_get_products_name
zen_get_products_model
zen_get_products_status
zen_get_product_is_linked
zen_get_products_stock (*)
zen_get_products_manufacturers_name
zen_get_products_manufacturers_image
zen_get_products_manufacturers_id
zen_get_products_url
zen_get_products_description
zen_get_info_page
zen_get_products_type
zen_get_products_image (er, well, must call zen_image yourself)
zen_get_products_virtual
zen_get_products_allow_add_to_cart
zen_get_product_is_always_free_shipping
zen_products_lookup
zen_get_parent_category_id
zen_has_product_discounts
zen_has_product_specials
zen_get_product_path
zen_get_products_price_is_free
zen_get_products_price_is_call
zen_get_products_price_is_priced_by_attributes
zen_get_products_quantity_order_min
zen_get_products_quantity_order_units
zen_get_products_quantity_order_max
zen_get_products_qty_box_status
zen_get_products_quantity_mixed

*/
