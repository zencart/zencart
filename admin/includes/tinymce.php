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

/* Editor GPL version to use (irrelevant if a non-GPL API Key is provided)
 * This implementation activates the TinyMCE using the free JSDelivr CDN, which only works in 'GPL' mode.
 * To use a newer version on CDN, set the number in TINYMCE_GPL_VERSION below.
 * See https://github.com/tinymce/tinymce-dist/tags for latest
 * but make sure it exists on https://www.jsdelivr.com/package/npm/tinymce .
 * Sometimes not all plugins are updated on jsdelivr, so might need to fallback to prior release to avoid console errors.
 */
const TINYMCE_GPL_VERSION = '7.6.1';

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$editor_doc_base_url = (function_exists('zen_catalog_base_link') ? zen_catalog_base_link() : '/');
$editor_assets_dir = $editor_doc_base_url . DIR_WS_EDITORS . 'tinymce/';
?>

<!-- bof: TinyMCE Editor init -->
<script title="TinyMCE-JQueryPlugin" src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-jquery@2/dist/tinymce-jquery.min.js"></script>
<?php
// Ensure API Key configuration entry is set; Can be overridden via an extra_configures or extra_datafiles file.
if (!defined('TINYMCE_EDITOR_API_KEY')) {
    define('TINYMCE_EDITOR_API_KEY', 'GPL');
}

if (strtoupper(TINYMCE_EDITOR_API_KEY) === 'GPL' || empty(TINYMCE_EDITOR_API_KEY)) { ?>
<script title="TinyMCE-JSCore" src="https://cdn.jsdelivr.net/npm/tinymce@<?= TINYMCE_GPL_VERSION ?>/tinymce.min.js" referrerpolicy="origin"></script>
<?php } else { ?>
<script title="TinyMCE-JSCore" src="https://cdn.tiny.cloud/1/<?= TINYMCE_EDITOR_API_KEY ?>/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<?php } ?>
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
<script title="TinyMCE-custom-config" src="<?= $editor_assets_dir . 'config.js' ?>"></script>
<script title="TinyMCE-initialize">
    // init the editor
    tinymce.init(tinymce_config_setup());
    function tinymce_config_setup() {
        // set the selector lookup used by Zen Cart to denote editor fields
        let editorConfig = {selector: '.editorHook:not(.noEditor)'}
        // and GPL as default, so no need to edit this file
        let licenseFree = {license_key: 'gpl'}
        let licenseKeyAdmin = {license_key: '<?= TINYMCE_EDITOR_API_KEY ?>'}

        let directoriesConfig = {
            document_base_url: "<?= $editor_doc_base_url ?>",
            content_css: "<?= $editor_assets_dir . 'custom.css' ?>",

        }
        // In case the override/custom config.js doesn't load or is not present, fallback to empty object.
        let customConfig = {};
        if (typeof myTinyMceConfig !== 'undefined') {
            customConfig = myTinyMceConfig;
        }

        // For future support, such as admin-configurable feature switches:
        let configOverrides = {};

        // merge all configs in order
        return {...editorConfig, ...directoriesConfig, ...licenseFree, ...licenseKeyAdmin, ...customConfig, ...configOverrides};
    }
</script>

<?php if (strtoupper(TINYMCE_EDITOR_API_KEY) !== 'GPL') { ?>
<style title="TinyMCEPremiumInlineMediaCSS">
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
