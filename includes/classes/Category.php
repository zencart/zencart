<?php
declare(strict_types=1);

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jul 23 New in v2.1.0-alpha1 $
 * based on Product
 * @var language $lng
 * @var queryFactory $db
 */

use Zencart\Traits\NotifierManager;

class Category
{
    use NotifierManager;

    protected array $data;
    protected array $languages;

    /** @deprecated use ->get('property') or ->getData()  */
    public array $fields;

    /** @deprecated use !exists()  */
    public bool $EOF = true;

    public function __construct(protected ?int $categories_id = null)
    {
        $this->initLanguages();

        if ($this->categories_id !== null) {
            $this->data = $this->loadProductDetails($this->categories_id);

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
        return $this->categories_id;
    }

    public function exists(): bool
    {
        return !empty($this->categories_id) && !empty($this->data);
    }
    public function isValid(): bool
    {
        return !empty($this->data);
    }

    public function status(): int
    {
        return (int)($this->data['products_status'] ?? 0);
    }

    public function getInfoPage(): string
    {
        return $this->getTypeHandler() . '_info';
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    protected function loadProductDetails(int $categories_id, ?int $language_id = null): array
    {
        global $db;

        $sql = "SELECT p.*
                FROM " . TABLE_CATEGORIES . " p
                WHERE p.categories_id = " . (int)$categories_id;
        $product = $db->Execute($sql, 1, true, 900);

        if ($product->EOF) {
            return [];
        }

        $data = $product->fields;
        $data['id'] = $data['categories_id'];
        $data['categories_id'] = $data['categories_id'];
        //$data['parent_category_id'] = $data['master_categories_id'];

        /**
         * Add $data['lang'][code] = [products_name, products_description, etc] for each language
         */
        $sql = "SELECT pd.*
                FROM " . TABLE_CATEGORIES_DESCRIPTION . " pd
                WHERE pd.categories_id = " . (int)$categories_id . "
                ORDER BY language_id";
        $pd = $db->Execute($sql, null, true, 900);
        foreach ($pd as $result) {
            unset($result['categories_id']);
            $data['lang'][$this->languages[$result['language_id']]] = $result;
        }

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
