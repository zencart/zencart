<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Tue Aug 7 15:42:16 2012 +0100 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

    if (zen_not_null($_POST)) {
      $pInfo = new objectInfo($_POST);
      $metatags_title = $_POST['metatags_title'];
      $metatags_keywords = $_POST['metatags_keywords'];
      $metatags_description = $_POST['metatags_description'];
    } else {
      $product = $db->Execute("select pd.products_name, p.products_model,
                                      p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                      p.products_id, p.metatags_price_status, p.metatags_title_tagline_status,
                                      mtpd.metatags_title, mtpd.metatags_keywords, mtpd.metatags_description
                              from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd
                              where p.products_id = '" . (int)$_GET['pID'] . "'
                              and p.products_id = pd.products_id
                              and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              and p.products_id = mtpd.products_id
                              and mtpd.language_id = '" . (int)$_SESSION['languages_id'] . "'");


      $pInfo = new objectInfo($product->fields);
    }

    $form_action = (isset($_GET['pID'])) ? 'update_product_meta_tags' : 'insert_product_meta_tags';

    echo zen_draw_form($form_action, $type_admin_handler, 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"');

    $languages = zen_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
        $pInfo->metatags_title = zen_get_metatags_title($pInfo->products_id, $languages[$i]['id']);
        $pInfo->metatags_keywords = zen_get_metatags_keywords($pInfo->products_id, $languages[$i]['id']);
        $pInfo->metatags_description = zen_get_metatags_description($pInfo->products_id, $languages[$i]['id']);
      } else {
        $pInfo->metatags_title = zen_db_prepare_input($metatags_title[$languages[$i]['id']]);
        $pInfo->metatags_keywords = zen_db_prepare_input($metatags_keywords[$languages[$i]['id']]);
        $pInfo->metatags_description = zen_db_prepare_input($metatags_description[$languages[$i]['id']]);
      }
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="3" cellspacing="4" cellpadding="6">
            <td class="main" valign="top">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . '<strong>' . TEXT_PRODUCTS_NAME . '</strong>' . '&nbsp;' . ($pInfo->metatags_products_name_status == '1' ? zen_get_products_name($_GET['pID'], $languages[$i]['id']) : TEXT_META_EXCLUDED); ?>
            </td>
            <td class="main" valign="top">
               <?php echo '<strong>' . TEXT_PRODUCTS_MODEL . '</strong>&nbsp;' . ($pInfo->metatags_model_status == '1' ? $pInfo->products_model : TEXT_META_EXCLUDED); ?>
            </td>
            <td class="main" valign="top">
               <?php echo '<strong>' . TEXT_PRODUCTS_PRICE_INFO . '</strong>&nbsp;' . ($pInfo->metatags_price_status == '1' ? $currencies->format($pInfo->products_price_sorter) : TEXT_META_EXCLUDED); ?>
            </td>
            <td class="main" valign="top">
               <?php echo '<strong>' . TEXT_PRODUCTS_METATAGS_TITLE_TAGLINE_STATUS . '</strong>&nbsp;' . ($pInfo->metatags_title_tagline_status == '1' ? TITLE . ' ' . SITE_TAGLINE : TEXT_META_EXCLUDED); ?>
            </td>

          <tr>
            <td class="main" valign="top"><?php echo TEXT_META_TAGS_TITLE; ?>&nbsp;</td>
            <td class="main" colspan="3"><?php echo ($pInfo->metatags_title_status == '1' ? $pInfo->metatags_title : TEXT_META_EXCLUDED) ; ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_META_TAGS_KEYWORDS; ?>&nbsp;</td>
            <td class="main" colspan="3"><?php echo $pInfo->metatags_keywords; ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_META_TAGS_DESCRIPTION; ?>&nbsp;</td>
            <td class="main" colspan="3"><?php echo $pInfo->metatags_description; ?></td>
          </tr>
        </table></td>
      </tr>
<?php
    }

    if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
      if (isset($_GET['origin'])) {
        $pos_params = strpos($_GET['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($_GET['origin'], 0, $pos_params);
          $back_url_params = substr($_GET['origin'], $pos_params + 1);
        } else {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = FILENAME_CATEGORIES;
        $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
      }
?>
      <tr>
        <td align="right"><?php echo '<a href="' . zen_href_link($back_url, $back_url_params, 'NONSSL') . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td align="right" class="smallText">
<?php
/* Re-Post all POST'ed variables */
      reset($_POST);
      while (list($key, $value) = each($_POST)) {
        if (!is_array($_POST[$key])) {
          echo zen_draw_hidden_field($key, zen_output_string_protected(stripslashes($value)));
        }
      }

      $languages = zen_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        echo zen_draw_hidden_field('metatags_title[' . $languages[$i]['id'] . ']', zen_output_string_protected(stripslashes($metatags_title[$languages[$i]['id']])));
        echo zen_draw_hidden_field('metatags_keywords[' . $languages[$i]['id'] . ']', zen_output_string_protected(stripslashes($metatags_keywords[$languages[$i]['id']])));
        echo zen_draw_hidden_field('metatags_description[' . $languages[$i]['id'] . ']', zen_output_string_protected(stripslashes($metatags_description[$languages[$i]['id']])));
      }

      echo zen_image_submit('button_back.gif', IMAGE_BACK, 'name="edit"') . '&nbsp;&nbsp;';

      if (isset($_GET['pID'])) {
        echo zen_image_submit('button_update.gif', IMAGE_UPDATE);
      } else {
        echo zen_image_submit('button_insert.gif', IMAGE_INSERT);
      }
      echo '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?>
        </td>
      </tr>
    </table></form>
<?php
    }
