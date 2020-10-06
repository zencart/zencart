<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 15 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// prepare list of languages supported by this website, so we can tell CKEditor
$var = zen_get_languages();
$jsLanguageLookupArray = "var lang = new Array;\n";
foreach ($var as $key)
{
  $jsLanguageLookupArray .= "        lang[" . $key['id'] . "] = '" . $key['code'] . "';\n";
}
?>
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"><\/script>');</script>
<script>window.jQuery || document.write('<script src="includes/javascript/jquery.min.js"><\/script>');</script>
<script src="https://cdn.ckeditor.com/4.14.0/standard-all/ckeditor.js" title="CKEditorCDN"></script>

<script title="ckEditor-Initialize">
    jQuery(document).ready(function() {
        <?php echo $jsLanguageLookupArray; ?>
        // Activate on every textarea field that has the editorHook class and does not have the noEditor class
        jQuery('textarea.editorHook').each(function() {
            if (!jQuery(this).hasClass('noEditor'))
            {
                // handle multi-language variants of fields
                index = jQuery(this).attr('name').match(/\d+/);
                if (index == null) index = <?php echo $_SESSION['languages_id'] ?>;

                CKEDITOR.replace(jQuery(this).attr('name'),
                    {
                        customConfig: '<?php echo (function_exists('zen_catalog_base_link') ? zen_catalog_base_link() : '/') . DIR_WS_EDITORS . 'ckeditor/config.js'; ?>',
                        language: lang[index]
                    });
            }
        });
    });
</script>

<?php
// Other options:
// - Edit your /editors/ckeditor/config.js file to control the toolbar buttons, add additional plugins, control the UI color, etc
//
// Advanced:
// https://ckeditor.com/docs/ckeditor4/latest/features/styles.html#the-stylesheet-parser-plugin
// - set custom styles in the /editors/ckeditor/styles.js file
// - import a template-specific stylesheet from catalog-side, using config.contentsCss setting, so dialogs "look like catalog-side" in admin editor
