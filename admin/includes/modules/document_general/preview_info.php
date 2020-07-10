<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 01 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$languages = zen_get_languages();
if (empty($products_description)) $products_description = [];
if (empty($products_name)) $products_name = [];
if (empty($products_url)) $products_url = [];

if (zen_not_null($_POST)) {
  $pInfo = new objectInfo($_POST);
  $products_name = $_POST['products_name'];
  $products_description = $_POST['products_description'];
  $products_url = $_POST['products_url'];
  foreach ($products_url as &$url){ // remove protocol
      $url = str_replace(array('http://', 'https://'), '', $url);
  }
  unset ($url);
} else {
  $product = $db->Execute("SELECT p.*,
                                  pd.language_id, pd.products_name, pd.products_description, pd.products_url
                           FROM " . TABLE_PRODUCTS . " p,
                                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                           WHERE p.products_id = pd.products_id
                           AND p.products_id = " . (int)$_GET['pID']);

  $pInfo = new objectInfo($product->fields);
  $products_image_name = $pInfo->products_image;

  foreach($product as $prod) {
    $products_name[$prod['language_id']] = $prod['products_name'];
    $products_description[$prod['language_id']] = $prod['products_description'];
    $products_url[$prod['language_id']] = $prod['products_url'];
  }
}

$form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';
?>
<div class="container-fluid">
    <?php
    if (!isset($_GET['read']) || ($_GET['read'] !== 'only')) {
      echo zen_draw_form($form_action, FILENAME_PRODUCT, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"');
    }

    for ($i = 0, $n = count($languages); $i < $n; $i++) {
      if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
        $pInfo->products_name = zen_get_products_name($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = zen_get_products_description($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_url = zen_get_products_url($pInfo->products_id, $languages[$i]['id']);
      } else {
        $pInfo->products_name = zen_db_prepare_input($products_name[$languages[$i]['id']]);
        $pInfo->products_description = zen_db_prepare_input($products_description[$languages[$i]['id']]);
        $pInfo->products_url = zen_db_prepare_input($products_url[$languages[$i]['id']]);
      }

      if (isset($_GET['pID'])) {
        $specials_price = zen_get_products_special_price($_GET['pID']);
      }
      ?>
<div class="row table-bordered">
    <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
    <div class="row img">
        <?php
//auto replace with defined missing image
        if (isset($_POST['products_image_manual']) && $_POST['products_image_manual'] != '') {
          $products_image_name = $_POST['img_dir'] . $_POST['products_image_manual'];
          $pInfo->products_name = $products_image_name;
        }
        if ((isset($_POST['image_delete']) && $_POST['image_delete'] == '1') || ($products_image_name == '' && PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1')) {
          echo zen_image(DIR_WS_CATALOG_IMAGES . PRODUCTS_IMAGE_NO_IMAGE, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="img-thumbnail" style="float:right;"') . $pInfo->products_description;
        } else {
          echo zen_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="img-thumbnail" style="float:right;"') . $pInfo->products_description;
        }
        ?>
    </div>
    <?php
    if ($pInfo->products_url) {
      ?>
      <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
      <div class="row"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></div>
      <?php
    }
    ?>
    <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
    <?php
    if ($pInfo->products_date_available > date('Y-m-d')) {
      ?>
      <div class="row"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, zen_date_long($pInfo->products_date_available)); ?></div>
      <?php
    } else {
      ?>
      <div class="row"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, zen_date_long($pInfo->products_date_added)); ?></div>
      <?php
    }
    ?>
    <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
</div>
<div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '5', '10'); ?></div>
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
      $back_url = FILENAME_CATEGORY_PRODUCT_LISTING;
      $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
    }
    ?>
    <div class="row text-right">
      <a href="<?php echo zen_href_link($back_url, $back_url_params . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
    </div>
    <?php
  } else {
    ?>
    <div class="row text-right">
        <?php
        /* Re-Post all POST'ed variables */
        foreach ($_POST as $key => $value) {
          if (!is_array($_POST[$key])) {
            echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, TRUE));
          }
        }

        for ($i = 0, $n = count($languages); $i < $n; $i++) {
          echo zen_draw_hidden_field('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_name[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
          echo zen_draw_hidden_field('products_description[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_description[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
          echo zen_draw_hidden_field('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_url[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
        }
        echo zen_draw_hidden_field('products_image', stripslashes($products_image_name));
        echo ( (isset($_GET['search']) && !empty($_GET['search'])) ? zen_draw_hidden_field('search', $_GET['search']) : '') . ( (isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? zen_draw_hidden_field('search', $_POST['search']) : '');
      ?>
        <button type="submit" name="edit" value="edit" class="btn btn-default"><?php echo IMAGE_BACK; ?></button>
      <?php
        if (isset($_GET['pID'])) {
          ?>
        <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
        <?php
      } else {
        ?>
        <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
        <?php
      }
      ?>
      <a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
    </div>
      <?php
  }
      if (!(isset($_GET['read']) && ($_GET['read'] === 'only'))) {
        echo '</form>';
  }
  ?>
</div>
