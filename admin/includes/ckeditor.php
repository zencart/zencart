<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
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
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"><\/script>');</script>
<script>window.jQuery || document.write('<script src="includes/javascript/jquery.min.js"><\/script>');</script>
<script src="https://cdn.ckeditor.com/4.22.1/standard-all/ckeditor.js" title="CKEditorCDN"></script>

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
