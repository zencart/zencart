<?php
/**
 * redirect handler
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 May 18 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$_GET['action'] = $_GET['action'] ?? '';

// -----
// Grab the currently-configured **default** language's id value.  Used
// to determine if an additional query is needed when a language-specific URL
// isn't found for the session's current language.
//
$configured_languages = new language();
$default_language_id = (int)$configured_languages->language['id'];
unset($configured_languages);

// -----
// Determine whick redirect is to be performed ...
//
switch ($_GET['action']) {
    case 'product':
        if (!empty($_GET['products_id'])) {
            $sql = 'SELECT products_url FROM ' . TABLE_PRODUCTS_DESCRIPTION . ' WHERE products_id = :productId: AND language_id = :languageId: LIMIT 1';
            $sql = $db->bindVars($sql, ':productId:', $_GET['products_id'], 'integer');
            $sql = $db->bindVars($sql, ':languageId:', $_SESSION['languages_id'], 'integer');
            $result = $db->Execute($sql);
            if (!$result->EOF && $result->fields['products_url'] !== '') {
                $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_PRODUCT', [], $_GET['products_id'], $_SESSION['languages_id']);
                zen_redirect(fixup_url($result->fields['products_url']));
            } elseif ($default_language_id !== $_SESSION['languages_id']) {
                $sql = 'SELECT products_url FROM ' . TABLE_PRODUCTS_DESCRIPTION . ' WHERE products_id = :productId: AND language_id = :languageId: LIMIT 1';
                $sql = $db->bindVars($sql, ':productId:', $_GET['products_id'], 'integer');
               $sql = $db->bindVars($sql, ':languageId:', $default_language_id, 'integer');
                $result = $db->Execute($sql);
                if (!$result->EOF && $result->fields['products_url'] !== '') {
                    $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_PRODUCT', [], $_GET['products_id'], $default_language_id);
                    zen_redirect(fixup_url($result->fields['products_url']));
                }
            }
        }
        break;

    case 'music_artist':
        if (!empty($_GET['artists_id'])) {
            $sql = 'SELECT artists_url FROM ' . TABLE_RECORD_ARTISTS_INFO . ' WHERE artists_id = :artistId: AND languages_id = :languageId: LIMIT 1';
            $sql = $db->bindVars($sql, ':artistId:', $_GET['artists_id'], 'integer');
            $sql = $db->bindVars($sql, ':languageId:', $_SESSION['languages_id'], 'integer');
            $result = $db->Execute($sql);
            if (!$result->EOF && $result->fields['artists_url'] !== '') {
                $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_MUSIC_ARTIST', [], $_GET['artists_id'], $_SESSION['languages_id']);
                zen_update_music_artist_clicked($_GET['artists_id'], $_SESSION['languages_id']);
                zen_redirect(fixup_url($result->fields['artists_url']));
            } elseif ($default_language_id !== $_SESSION['languages_id']) {
                $sql = 'SELECT artists_url FROM ' . TABLE_RECORD_ARTISTS_INFO . ' WHERE artists_id = :artistId: AND languages_id = :languageId: LIMIT 1';
                $sql = $db->bindVars($sql, ':artistId:', $_GET['artists_id'], 'integer');
                $sql = $db->bindVars($sql, ':languageId:', $default_language_id, 'integer');
                $result = $db->Execute($sql);
                if (!$result->EOF && $result->fields['artists_url'] !== '') {
                    $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_MUSIC_ARTIST', [], $_GET['artists_id'], $default_language_id);
                    zen_update_music_artist_clicked($_GET['artists_id'], $default_language_id);
                    zen_redirect(fixup_url($result->fields['artists_url']));
                }
            }
        }
        break;

    case 'music_record_company':
        if (!empty($_GET['record_company_id'])) {
            $sql = 'SELECT record_company_url FROM ' . TABLE_RECORD_COMPANY_INFO . ' WHERE record_company_id = :rcId: AND languages_id = :languageId: LIMIT 1';
            $sql = $db->bindVars($sql, ':rcId:', $_GET['record_company_id'], 'integer');
            $sql = $db->bindVars($sql, ':languageId:', $_SESSION['languages_id'], 'integer');
            $result = $db->Execute($sql);
            if (!$result->EOF && $result->fields['record_company_url'] !== '') {
                $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_RECORD_COMPANY', [], $_GET['record_company_id'], $_SESSION['languages_id']);
                zen_update_record_company_clicked($_GET['record_company_id'], $_SESSION['languages_id']);
                zen_redirect(fixup_url($result->fields['record_company_url']));
            } elseif ($default_language_id !== $_SESSION['languages_id']) {
                $sql = 'SELECT record_company_url FROM ' . TABLE_RECORD_COMPANY_INFO . ' WHERE record_company_id = :rcId: AND languages_id = :languageId: LIMIT 1';
                $sql = $db->bindVars($sql, ':rcId:', $_GET['record_company_id'], 'integer');
                $sql = $db->bindVars($sql, ':languageId:', $default_language_id, 'integer');
                $result = $db->Execute($sql);
                if (!$result->EOF && $result->fields['record_company_url'] !== '') {
                    $zco_notifier->notify('NOTIFY_BEFORE_REDIRECT_ACTION_RECORD_COMPANY', [], $_GET['record_company_id'], $default_language_id);
                    zen_update_record_company_clicked($_GET['record_company_id'], $default_language_id);
                    zen_redirect(fixup_url($result->fields['record_company_url']));
                }
            }
        }
        break;

    case 'banner':
        if (!empty($_GET['goto'])) {
            $banner_query = 'SELECT banners_url FROM ' . TABLE_BANNERS . ' WHERE banners_id = :bannersID LIMIT 1';
            $banner_query = $db->bindVars($banner_query, ':bannersID', $_GET['goto'], 'integer');
            $banner = $db->Execute($banner_query);
            if (!$banner->EOF && $banner->fields['banners_url'] !== '') {
                $zco_notifier->notify(
                    'NOTIFY_BEFORE_REDIRECT_ACTION_BANNER',
                    [
                        'banners_id' => (int)$_GET['goto'],
                        'banners_url' => $banner->fields['banners_url'],
                    ]
                );
                zen_update_banner_click_count($_GET['goto']);
                zen_redirect($banner->fields['banners_url']);
            }
        }
        break;

    case 'manufacturer':
        if (!empty($_GET['manufacturers_id'])) {
            $sql = 'SELECT manufacturers_url FROM ' . TABLE_MANUFACTURERS_INFO . ' WHERE manufacturers_id = :manufacturersID AND languages_id = :languagesID LIMIT 1';
            $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
            $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
            $manufacturer = $db->Execute($sql);
            if (!$manufacturer->EOF && $manufacturer->fields['manufacturers_url'] !== '') {
                $zco_notifier->notify(
                    'NOTIFY_BEFORE_REDIRECT_ACTION_MANUFACTURER',
                    [
                        'manufacturers_id' => $_GET['manufacturers_id'],
                        'manufacturers_url' => $manufacturer->fields['manufacturers_url'],
                        'language_id' => $_SESSION['languages_id'],
                    ]
                );
                $sql =
                    'UPDATE ' . TABLE_MANUFACTURERS_INFO . '
                        SET url_clicked = url_clicked+1, date_last_click = now()
                      WHERE manufacturers_id = :manufacturersID
                        AND languages_id = :languagesID
                      LIMIT 1';

                $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
                $sql = $db->bindVars($sql, ':languagesID', $_SESSION['languages_id'], 'integer');
                $db->Execute($sql);
                zen_redirect(fixup_url($manufacturer->fields['manufacturers_url']));
            } elseif ($default_language_id !== $_SESSION['languages_id']) {
                // no url exists for the selected language, lets use the default language then
                $sql = 'SELECT manufacturers_url FROM ' . TABLE_MANUFACTURERS_INFO . ' WHERE manufacturers_id = :manufacturersID AND languages_id = :languagesID LIMIT 1';
                $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
                $sql = $db->bindVars($sql, ':languagesID', $default_language_id, 'integer');
                $manufacturer = $db->Execute($sql);
                if (!$manufacturer->EOF  && $manufacturer->fields['manufacturers_url'] !== '') {
                    $zco_notifier->notify(
                        'NOTIFY_BEFORE_REDIRECT_ACTION_MANUFACTURER',
                        [
                            'manufacturers_id' => $_GET['manufacturers_id'],
                            'manufacturers_url' => $manufacturer->fields['manufacturers_url'],
                            'language_id' => $default_language_id,
                        ]
                    );
                    $sql =
                        'UPDATE ' . TABLE_MANUFACTURERS_INFO . '
                            SET url_clicked = url_clicked+1, date_last_click = now()
                          WHERE manufacturers_id = :manufacturersID
                            AND languages_id = :languagesID
                          LIMIT 1';

                    $sql = $db->bindVars($sql, ':manufacturersID', $_GET['manufacturers_id'], 'integer');
                    $sql = $db->bindVars($sql, ':languagesID', $default_language_id, 'integer');
                    $db->Execute($sql);
                    zen_redirect(fixup_url($manufacturer->fields['manufacturers_url']));
                }
            }
        }
        break;

    default:
        $zco_notifier->notify('NOTIFY_REDIRECT_DEFAULT_ACTION', $default_language_id);
        break;
}
zen_redirect(zen_href_link(FILENAME_DEFAULT));
