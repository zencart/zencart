<?php

abstract class ZenShipping extends base
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $code determines the internal 'code' name used to designate "this" shipping module
     *
     * @var string
     */
    public string $code;
    /**
     * $description is a soft name for this shipping method
     * @var string
     */
    public string $description;
    /**
     * $enabled determines whether this module shows or not... during checkout.
     * @var boolean
     */
    public bool $enabled;
    /**
     * $debug is an array containing debug information
     * @var array
     */
    public array $debug = [];
    /**
     * $icon is the file name containing the Shipping method icon
     * @var string
     */
    public string $icon;
    /**
     * $quotes is an array containing all the quote information for this shipping module
     * @var array
     */
    public array $quotes;
    /**
     * $sort_order is the order priority of this shipping module when displayed
     * @var int|null
     */
    public $sort_order;
    /**
     * $tax_basis is used to indicate if tax is based on shipping, billing or store address.
     * @var string
     */
    public string $tax_basis;
    /**
     * $tax_class is the  Tax class to be applied to the shipping cost
     * @var string
     */
    public $tax_class;
    /**
     * $title is the displayed name for this shipping method
     * @var string
     */
    public string $title;

    abstract public function quote($method = ''): array;

    abstract public function keys(): array;

    abstract public function install(): void;

    /**
     * Remove the module's settings
     *
     */
    public function remove(): void
    {
        global $db;
        $db->Execute(
            "DELETE FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')"
        );
    }

    /**
     * Disable the module if a shipping-zone has been defined and the
     * order isn't to be delivered to that zone.
     */
    protected function checkEnabledForZone(string $zone_id): void
    {
        global $db, $order;
        if ((int)$zone_id > 0) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                  WHERE geo_zone_id = " . (int)$zone_id . "
                    AND zone_country_id = " . (int)($order->delivery['country']['id'] ?? -1) . "
                  ORDER BY zone_id"
            );
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($next_zone['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
            }
        }
    }
}
