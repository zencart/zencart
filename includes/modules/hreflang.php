<?php
/**
 * hreflang module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 06 New in v2.1.0-beta1 $
 *
 * @var notifier $zco_notifier
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// BOF hreflang for multilingual sites
if (!isset($lng) || !$lng instanceof language) {
    $lng = new language;
}
if (method_exists($lng, 'get_language_list')) {
    $languages = $lng->get_language_list();
} else {
    // fallback for pre-v2.0.0 with old language class
    $languages = array_keys($lng->catalog_languages);
}

$bypass = false;
$zco_notifier->notify('NOTIFY_MODULE_START_HREFLANG', $current_page_base, $bypass, $lng, $languages, $canonicalLink);
if ($bypass) {
    return;
}

if (count($languages) <= 1) {
    // skip when site has only one language
    return;
}
if (empty($canonicalLink)) {
    // canonical link is needed
    return;
}

foreach($languages as $key) {
    if ($this_is_home_page) {
        $link = zen_href_link(FILENAME_DEFAULT, 'language=' . $key, $request_type, false);
    } else {
        $link = $canonicalLink . (str_contains($canonicalLink, '?') ? '&amp;' : '?') . 'language=' . $key;
    }
    echo '<link rel="alternate" hreflang="' . $key . '" href="' . $link . '"/>' . "\n";
}
// include x-default
echo '<link rel="alternate" hreflang="x-default" href="' . $canonicalLink . '"/>' . "\n";

