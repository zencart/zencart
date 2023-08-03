<?php
$define = [
    'MODULE_SHIPPING_STOREPICKUP_TEXT_TITLE' => 'Store Pickup',
    'MODULE_SHIPPING_STOREPICKUP_TEXT_DESCRIPTION' => 'Customer In Store Pick-up',
    'MODULE_SHIPPING_STOREPICKUP_TEXT_WAY' => 'Walk In',
    'MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS' => '',
];

// MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS is for defining multiple locations/methods on a per-language basis. It is only used if the shopper has selected a language other than the store's default.
// The content of the MODULE_SHIPPING_STOREPICKUP_MULTIPLE_WAYS definition should be the same as the multiple locations in the shipping module's settings in your admin, just with names changed.
// Typical formats are:
// "Location One, 5.00; Location Two, 3.50; Location Three, 0.00"
// "Location One, Location Two, Location Three"
// or leave it blank to simply use the same text as defined in the Admin, regardless of language
// TIP: This should really be left blank for the default language, otherwise the Admin settings field is never used.

return $define;
