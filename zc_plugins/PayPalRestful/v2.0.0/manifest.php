<?php
return [
    'pluginVersion' => 'v2.0.0',
    'pluginName' => "PayPal Restful Payment Module",
    'pluginDescription' => "This Zen Cart payment module combines the processing for the <b>PayPal Payments Pro</b> (<var>paypaldp</var>) and <b>PayPal Express Checkout</b> (<var>paypalwpp</var>) payment modules that are currently built into the Zen Cart distribution.  Instead of using the older NVP (<b>N</b>ame <b>V</b>alue <b>P</b>air) methods to communicate with PayPal, this payment module uses PayPal's now-current <a href="https://developer.paypal.com/api/rest/" target="_blank">REST APIs</a> and combines the two legacy methods into one.<br><br>Based on the like-named unencapsulated v1.3.1 version of the payment module.",
    'pluginAuthor' => 'Vinos de Frutas Tropicales (lat9)',
    'pluginId' => 0, // ID from Zen Cart forum
    'zcVersions' => [],
    'changelog' => '', // online URL (eg github release tag page, or changelog file there) or local filename only, ie: changelog.txt (in same dir as this manifest file)
    'github_repo' => '', // url
    'pluginGroups' => [],
];
