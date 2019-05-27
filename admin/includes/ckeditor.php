<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2010 Kuroi Web Design
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Apr 25 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$var = zen_get_languages();
$jsLanguageLookupArray = "var lang = new Array;\n";
foreach ($var as $key)
{
  $jsLanguageLookupArray .= "  lang[" . $key['id'] . "] = '" . $key['code'] . "';\n";
}
?>
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.4.0.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"><\/script>');</script>
<script>window.jQuery || document.write('<script src="includes/javascript/jquery-3.4.0.min.js"><\/script>');</script>
<script type="text/javascript" src="../<?php echo DIR_WS_EDITORS; ?>ckeditor/ckeditor.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  <?php echo $jsLanguageLookupArray ?>
  $('textarea').each(function() {
    if ($(this).hasClass('editorHook') || ($(this).attr('name') != 'message' && ! $(this).hasClass('noEditor')))
    {
      index = $(this).attr('name').match(/\d+/);
      if (index == null) index = <?php echo $_SESSION['languages_id'] ?>;
      CKEDITOR.replace($(this).attr('name'),
        {
          coreStyles_underline : { element : 'u' },
          width : '100%',
          language: lang[index]
        });
    }
  });
});
</script>
