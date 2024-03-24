<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified 2024-02-24 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
?>

<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"><\/script>');</script>
<script>window.jQuery || document.write('<script src="includes/javascript/jquery.min.js"><\/script>');</script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js" title="CKEditorCDN"></script>
<?php

// prepare list of languages supported by this website, so we can tell CKEditor
$var = zen_get_languages();
$jsLanguageLookupArray = "var lang = new Array;\n";
foreach ($var as $key)
{
  $jsLanguageLookupArray .= "        lang[" . $key['id'] . "] = '" . $key['code'] . "';\n";
  if ($key['code'] === 'en') {
      continue;
  }
?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/translations/<?php echo $key['code']; ?>.js"></script>
<?php
}
?>
<script src="<?php echo (function_exists('zen_catalog_base_link') ? zen_catalog_base_link() : '/') . DIR_WS_EDITORS . 'ckeditor5/config.js'; ?>"></script>
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

                ClassicEditor
                    .create(document.querySelector( '#' + jQuery(this).attr('id') ),
                        {...myCKEditorConfig, ...{language: lang[index]}})
                    .catch( error => {
                        console.error( error );
                    } );
            }
        });
    });
</script>

<?php
// Other options:
// - Edit your /editors/ckeditor5/config.js file to control the toolbar buttons, add additional plugins, etc
