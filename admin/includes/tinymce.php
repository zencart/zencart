<?php
/**
 * TinyMCE Editor
 *
 * Custom config can be set up in /editors/tinymce/config.js
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte New in v2.2.0 $
 *
 * Ref: https://www.tiny.cloud/docs/tinymce/latest/bootstrap-zip/
 * Ref: https://www.jsdelivr.com/package/npm/tinymce
 * Ref: https://github.com/tinymce/tinymce-dist/tags
 * Ref: https://www.tiny.cloud/docs/tinymce/latest/upgrading/#upgrading-tinymce-self-hosted-manually
 * Ref: https://www.tiny.cloud/docs/tinymce/latest/ui-localization/
 */
/*
 * TinyMCE can run in one of 4 modes:
 * 1. Commercial License, with paid access to advanced features
 *    To enable your Commercial License, simply enter your subscribed API Key in Admin->Configuration->My Store->TinyMCE API Key, and the editor will be loaded via tiny.cloud CDN.
 *    You will need to add your store's domain name into your subscription account on https://www.tiny.cloud/my-account/domains/
 * 2. Free License with a Tiny Cloud account
 *    This mode imposes some feature limits, but is a good way to start a free trial for advanced features. Enter your account API key, as above, but without paying a subscription.
 * 3. Free GPL self-hosted mode
 *    This gives you basic editor features, and allows you to add custom plugins or custom localizations, but requires technical understanding.
 *    To use this mode, obtain the TinyMCE "release zip", and unzip it to your store's /editors/tinymce directory, and set "GPL" as your TinyMCE API Key
 * 4. Free GPL via CDN mode, provided by the JSDelivr.com CDN. (Some countries might block access to JSDeliver.com; in this case, use self-hosted, above)
 *    To use this mode, simply set "GPL" as your TinyMCE API Key. This is the default configuration with a new Zen Cart install.
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$tinymceVersionSeries = 8; // should be single-digit int or string.
$tinymceFallbackCDNversion = match((string)$tinymceVersionSeries) {
    '7' => '7.9.1',
    '8' => '8.0.2',
    default => '8.0.2',
};

/**
 * In case the user has selected GPL (and is not self-hosted), and in case we cannot calculate the latest via github, the following is a fallback version we've tested with.
 * See https://github.com/tinymce/tinymce-dist/tags for latest, but make sure it exists on https://www.jsdelivr.com/package/npm/tinymce
 */
function zenGetLatestTinyMceReleaseTag(int|string $majorVersion = 0): string|false
{
    $url = 'https://api.github.com/repos/tinymce/tinymce-dist/tags';
    $response = zenDoCurlRequest($url);
    if (empty($response)) {
        return false;
    }
    $tagInfo = json_decode($response, true);

    if (empty($tagInfo) || !is_array($tagInfo)) {
        return false;
    }

    if (empty($majorVersion)) {
        return $tagInfo[0]['name'] ?? false;
    }

    // If a specific major version is requested, return most recent
    foreach ($tagInfo as $key => $tag) {
        if (str_starts_with($tag['name'], (string)$majorVersion)) {
            return $tag['name'];
        }
    }
    return false;
}

// Ensure API Key configuration entry is set; Can be overridden via an extra_configures or extra_datafiles file.
if (!defined('TINYMCE_EDITOR_API_KEY')) {
    $db->Execute("INSERT IGNORE INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('TinyMCE Editor API Key', 'TINYMCE_EDITOR_API_KEY', 'GPL', 'Basic editor features are free, in GPL mode.<br>Optionally enable premium editor features in the TinyMCE editor by providing your account API key and register your store website domain in your Tiny account.<br>Sign up at <a href=\"https://www.tiny.cloud/auth/signup/\" target=\"_blank\">www.tiny.cloud</a><br><br>Default value: <strong>GPL</strong> for free-unregistered mode with basic features.', 1, 111, now())");
    // the following will be ignored on next load of the page, so should not be edited here
    define('TINYMCE_EDITOR_API_KEY', 'GPL');
}

// Language Support Setup
$lng ??= new language;

// Some of these are output in the js config:
$editor_doc_base_url = (function_exists('zen_catalog_base_link') ? zen_catalog_base_link() : DIR_WS_CATALOG);
$editor_assets_url = $editor_doc_base_url . DIR_WS_EDITORS . 'tinymce/';
$editor_assets_path = DIR_FS_CATALOG . DIR_WS_EDITORS . 'tinymce/';

// Determine whether the jQuery patch is self-hosted
$editor_jquery_patch_filename_url = $editor_assets_url . 'tinymce-jquery.min.js';
$editor_jquery_patch_filename_path = $editor_assets_path . 'tinymce-jquery.min.js';
$editor_jquery_patch_src = file_exists($editor_jquery_patch_filename_path) ? $editor_jquery_patch_filename_url : 'https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@2/dist/tinymce-jquery.min.js';

// Determine whether TinyMCE editor JS files are self-hosted. If yes, use it. If not, use CDN. But if not GPL then use TinyCloud CDN with API key.
if (str_starts_with(strtoupper(TINYMCE_EDITOR_API_KEY), 'GPL') || empty(TINYMCE_EDITOR_API_KEY)) {
    $tinymceCDNversion = $tinymceFallbackCDNversion;
    if (function_exists('zenDoCurlRequest') && $editor_latest_tag = zenGetLatestTinyMceReleaseTag($tinymceVersionSeries)) {
        $tinymceCDNversion = $editor_latest_tag;
    }
    $editor_js_filename_url = $editor_assets_url . 'tinymce.min.js';
    $editor_js_filename_path = $editor_assets_path . 'tinymce.min.js';
    $editor_js_src = file_exists($editor_js_filename_path) ? $editor_js_filename_url : "https://cdn.jsdelivr.net/npm/tinymce@$tinymceCDNversion/tinymce.min.js";
} else {
    $editor_js_src = "https://cdn.tiny.cloud/1/" . TINYMCE_EDITOR_API_KEY . "/tinymce/$tinymceVersionSeries/tinymce.min.js";
}
?>

<!-- bof: TinyMCE Editor init -->
<script title="TinyMCE-JQueryPlugin" src="<?= $editor_jquery_patch_src ?>"></script>
<script title="TinyMCE-JSCore" src="<?= $editor_js_src ?>" referrerpolicy="origin"></script>
<style title="TinyMCE-BootstrapCompatibility">
    /* for Bootstrap compatibility to prevent interfering with tables */
    .tox-tinymce .table {
        width: auto;
    }
</style>
<script title="TinyMCE-BootstrapFix">
// Prevent Bootstrap 3/4 dialog from blocking focusin
$(document).on('focusin', function(e) {
  if ($(e.target).closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
    e.stopImmediatePropagation();
  }
});
// Prevent Bootstrap 5+ dialog from blocking focusin
document.addEventListener('focusin', (e) => {
  if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
    e.stopImmediatePropagation();
  }
});
</script>
<script title="TinyMCE-custom-config" src="<?= $editor_assets_url . 'config.js' ?>"></script>
<script title="TinyMCE-initialize">
    // init the editor
    tinymce.init(tinymce_config_setup());
    function tinymce_config_setup() {
        // Zen Cart denotes editor fields via .editorHook:not(.noEditor)
        let editorConfig = {selector: '.editorHook:not(.noEditor)'}
        // set GPL as default in case no license key has been set to override it
        let licenseFree = {license_key: 'gpl', promotion: false}
        let licenseKeyAdmin = {license_key: '<?= !empty(TINYMCE_EDITOR_API_KEY) ? TINYMCE_EDITOR_API_KEY : 'gpl' ?>'}

        let directoriesConfig = {
            document_base_url: "<?= $editor_doc_base_url ?>",
            content_css: "<?= $editor_assets_url . 'custom.css' ?>",

        }
        let languagesConfig = {
            language: '<?= zen_output_string_protected($_SESSION['languages_code']) ?>',
            content_langs: [

            <?php
            foreach ($lng->get_languages_by_code() as $lang) {
                echo "    { title: '" . zen_output_string_protected($lang['name']) . "', code: '" . zen_output_string_protected($lang['code']) . "' },\n";
            }
            ?>
            ],
        }
        // In case the override/custom config.js doesn't load or is not present, fallback to empty object.
        let customConfig = {};
        if (typeof myTinyMceConfig !== 'undefined') {
            customConfig = myTinyMceConfig;
        }

        // configOverrides for future support, such as admin-configurable feature switches:
        let configOverrides = {
            //skin: 'oxide',
            //icons: '',
        };

        // merge all configs in order
        return {...editorConfig, ...directoriesConfig, ...licenseFree, ...licenseKeyAdmin, ...languagesConfig, ...customConfig, ...configOverrides};
    }
</script>

<?php if (strtoupper(TINYMCE_EDITOR_API_KEY) !== 'GPL') { ?>
<style title="TinyMCEPremiumInlineMediaCSSsupport">
    .ephox-summary-card {
        border: 1px solid #AAA;
        box-shadow: 0 2px 2px 0 rgba(0,0,0,.14), 0 3px 1px -2px rgba(0,0,0,.2), 0 1px 5px 0 rgba(0,0,0,.12);
        padding: 10px;
        overflow: hidden;
        margin-bottom: 1em;
    }

    .ephox-summary-card a {
        text-decoration: none;
        color: inherit;
    }

    .ephox-summary-card a:visited {
        color: inherit;
    }

    .ephox-summary-card-title {
        font-size: 1.2em;
        display: block;
    }

    .ephox-summary-card-author {
        color: #999;
        display: block;
        margin-top: 0.5em;
    }

    .ephox-summary-card-website {
        color: #999;
        display: block;
        margin-top: 0.5em;
    }

    .ephox-summary-card-thumbnail {
        max-width: 180px;
        max-height: 180px;
        margin-left: 2em;
        float: right;
    }

    .ephox-summary-card-description {
        margin-top: 0.5em;
        display: block;
    }
</style>
<?php } ?>
<!-- eof: TinyMCE Editor init -->
