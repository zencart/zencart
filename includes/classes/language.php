<?php

/**
 * language Class.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 11 Modified in v2.0.0-beta1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Track configured languages and currently-selected language for customer session
 *
 */
class language extends base
{
    /**
     * Current Language
     */
    public array $language = [];

    /**
     * @deprecated
     * Legacy array of language codes.
     * Core ZC before v2.0 depended on this array's "keys" in html_header.php for generating alt-lang hrefs.
     * After v2.0 it calls get_languages()
     */
    public array $catalog_languages = [];

    /**
     * Languages the store has awareness of (and language definitions for)
     * Array key is language CODE (typically 2- or 3-char ISO, lowercase), defined by storeowner in Admin when configuring the language pack.
     */
    protected array $languages_by_code = [];

    /**
     * Array mapping Language ID keys to Language details
     */
    protected array $languages_by_id = [];

    /**
     * Supported languages as reported by current user's browser
     */
    protected array $browser_languages = [];


    public function __construct(string $language = '')
    {
        $this->build_list_of_configured_languages();

        $this->set_language($language);
    }

    /**
     * Query database for registered languages
     */
    protected function build_list_of_configured_languages(): void
    {
        global $db;

        $this->languages_by_code = [];
        $sql = "SELECT languages_id, name, code, image, directory
                FROM " . TABLE_LANGUAGES . "
                ORDER BY sort_order";
        $results = $db->Execute($sql);

        foreach ($results as $result) {
            // language array keyed on CODE, with configured details as sub-array values
            $this->languages_by_code[$result['code']] = [
                'id' => $result['languages_id'],
                'name' => $result['name'],
                'image' => $result['image'],
                'code' => $result['code'],
                'directory' => $result['directory'],
            ];

            // language array keyed on ID
            $this->languages_by_id[$result['languages_id']] = [
                'id' => $result['languages_id'],
                'name' => $result['name'],
                'image' => $result['image'],
                'code' => $result['code'],
                'directory' => $result['directory'],
            ];
        }

        // legacy support:
        $this->catalog_languages = $this->languages_by_code;
    }

    /**
     * Retrieve languages, for multilang iteration
     * Array keys are language code, and values are configuration details (id/name/image/code/directory)
     * Note: Admin function zen_get_languages() is a proxy to this function.
     */
    public function get_languages_by_code(): array
    {
        return $this->languages_by_code;
    }

    /**
     * Retrieve languages, for multilang iteration
     * Array keys are languages_id from db, and values are configuration details (id/name/image/code/directory)
     * Note: Admin function zen_get_languages() is a proxy to this function.
     */
    public function get_languages_by_id(): array
    {
        return $this->languages_by_id;
    }

    /**
     * Retrieve language as an array whose values are short language codes as configured by admin
     * ie: [1 => 'en', 2 => 'fr']
     *
     * Legacy note: Before v2.0, html_header.php formerly queried $this->catalog_languages as a public property but only used the key.
     */
    public function get_language_list(): array
    {
        $retVal = [];
        foreach ($this->languages_by_id as $value) {
            $retVal[$value['id']] = $value['code'];
        }
        return $retVal;
    }

    /**
     * Set $this->language to array of specified language code
     * Used by template and language loading mechanisms
     */
    public function set_language(string $language): void
    {
        if (empty($language) || !isset($this->languages_by_code[$language])) {
            $language = DEFAULT_LANGUAGE;
        }

        $this->language = $this->languages_by_code[$language];
    }

    /**
     * Parse browser headers for supported languages, and set our instance accordingly, if we support it.
     */
    public function get_browser_language(): void
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return;
        }

        $this->browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        foreach ($this->browser_languages as $val) {
            $lang = explode(';', $val);

            if (strlen($lang[0]) === 2) {
                $code = $lang[0];
            } elseif (strpos($lang[0], '-') === 2 || strpos($lang[0], '_') === 2) {
                $code = substr($lang[0], 0, 2);
            } else {
                continue;
            }

            if (isset($this->languages_by_code[$code])) {
                $this->language = $this->languages_by_code[$code];
                break;
            }
        }
    }
}
