<?php
declare(strict_types=1);

/**
 * Invalidate the cached sidebox data whenever the catalog tables behind it are
 * written.
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: new in v2.3.0 $
 */

/**
 * Watches every query for a write to the four tables that feed the categories
 * and manufacturers sideboxes, and drops their caches when it sees one.
 *
 * This is the invalidation hook because the per-event notifiers cannot do the
 * job: there is no notifier on category or product deletion, on the status
 * toggles, on move or copy, or anywhere in admin/products_to_categories.php.
 * Watching the query layer instead covers all of those from one place, plus the
 * paths no admin hook could ever see -- feed importers, cron jobs, CLI scripts
 * and plugins that write through $db.
 *
 * It does not cover changes made outside the application altogether, such as SQL
 * run in phpMyAdmin. Those are picked up when the cache lifetime expires, or
 * immediately via the "Clear Sidebox Caches" button in Store Manager.
 *
 * @since ZC v2.3.0
 */
class zcObserverSideboxCacheInvalidation extends base
{
    /**
     * Clearing twice in one request would be wasted work: a single product save
     * writes several of these tables.
     */
    protected bool $alreadyCleared = false;

    /**
     * Matches a write to one of the watched tables. Built once, because this is
     * consulted for every query in the request.
     */
    protected string $watchPattern;

    public function __construct()
    {
        $tables = [
            TABLE_CATEGORIES,
            TABLE_PRODUCTS,
            TABLE_PRODUCTS_TO_CATEGORIES,
            TABLE_MANUFACTURERS,
        ];

        /**
         * The word boundaries matter: each watched name is a prefix of other
         * table names that are written without affecting these sideboxes. The
         * storefront bumps manufacturers_info.url_clicked every time a customer
         * follows a manufacturer link (see the redirect page), and an ordinary
         * product save writes products_description and products_attributes. A
         * loose match on 'manufacturers' or 'products' would throw the cache
         * away for those, which on a crawled store is worse than not caching.
         *
         * \b does not match between 'products' and 'products_description',
         * because the underscore is a word character, so the prefix tables are
         * excluded without having to enumerate them.
         */
        $this->watchPattern = '~\b(' . implode('|', array_map('preg_quote', $tables)) . ')\b~i';

        $this->attach($this, ['NOTIFY_QUERY_FACTORY_EXECUTE_END']);
    }

    /**
     * @param object $class
     * @param string $eventID
     * @param array $payload
     * @since ZC v2.3.0
     */
    public function updateNotifyQueryFactoryExecuteEnd(&$class, $eventID, $payload = [])
    {
        if ($this->alreadyCleared) {
            return;
        }

        if (!$this->isWriteToWatchedTable((string)($payload['sql'] ?? ''))) {
            return;
        }

        zen_clear_category_map_cache();
        zen_clear_manufacturers_box_cache();

        $this->alreadyCleared = true;
    }

    /**
     * True when the statement changes rows in one of the watched tables.
     *
     * Ordered so that the cheap test rejects the common case first: nearly every
     * query in a request is a SELECT, and those never reach the regex.
     *
     * @since ZC v2.3.0
     */
    protected function isWriteToWatchedTable(string $sql): bool
    {
        $sql = ltrim($sql);
        if ($sql === '' || strncasecmp($sql, 'SELECT', 6) === 0) {
            return false;
        }

        $isWrite = strncasecmp($sql, 'INSERT', 6) === 0
            || strncasecmp($sql, 'UPDATE', 6) === 0
            || strncasecmp($sql, 'DELETE', 6) === 0
            || strncasecmp($sql, 'REPLACE', 7) === 0
            || strncasecmp($sql, 'TRUNCATE', 8) === 0;

        if (!$isWrite) {
            return false;
        }

        return preg_match($this->watchPattern, $sql) === 1;
    }
}
