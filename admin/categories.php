<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Feb 28 Modified in v1.5.6b $
 */
require('includes/application_top.php');
$languages = zen_get_languages();
$parameters = array(
  'categories_name' => '',
  'categories_description' => '',
  'categories_image' => '',
  'sort_order' => ''
);
$cInfo = new objectInfo($parameters);
$categoryId = (isset($_GET['cID']) ? $_GET['cID'] : '');
if ($categoryId != '') {
  $category = $db->Execute("SELECT c.categories_id, cd.categories_name, cd.categories_description, c.categories_image,
                                   c.sort_order, c.date_added, c.last_modified
                            FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                            WHERE c.categories_id = " . $categoryId . "
                            AND c.categories_id = cd.categories_id
                            AND cd.language_id = " . (int)$_SESSION['languages_id']);
  $cInfo->updateObjectInfo($category->fields);
}
$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (isset($_GET['page'])) {
  $_GET['page'] = (int)$_GET['page'];
}
if (isset($_GET['product_type'])) {
  $_GET['product_type'] = (int)$_GET['product_type'];
}
if (isset($_GET['cID'])) {
  $_GET['cID'] = (int)$_GET['cID'];
}

$zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);

if (zen_not_null($action)) {
  switch ($action) {

    case 'remove_type':
      if (isset($_POST['type_id'])) {
        $sql = "DELETE FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                WHERE category_id = " . (int)zen_db_prepare_input($_GET['cID']) . "
                AND product_type_id = " . (int)zen_db_prepare_input($_POST['type_id']);

        $db->Execute($sql);
        zen_remove_restrict_sub_categories($_GET['cID'], (int)$_POST['type_id']);
        $action = "edit";
        zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'action=edit_category&cPath=' . $_GET['cPath'] . '&cID=' . zen_db_prepare_input($_GET['cID'])));
      }
      break;
    case 'insert_category':
    case 'update_category':
      if (isset($_POST['add_type']) || isset($_POST['add_type_all'])) {
        // check if it is already restricted
        $sql = "select *
                from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                where category_id = '" . (int)zen_db_prepare_input($_POST['categories_id']) . "'
                and product_type_id = '" . (int)zen_db_prepare_input($_POST['restrict_type']) . "'";

        $type_to_cat = $db->Execute($sql);
        if ($type_to_cat->RecordCount() < 1) {
          //@@TODO find all sub-categories and restrict them as well.

          $insert_sql_data = array(
            'category_id' => zen_db_prepare_input($_POST['categories_id']),
            'product_type_id' => zen_db_prepare_input($_POST['restrict_type']));

          zen_db_perform(TABLE_PRODUCT_TYPES_TO_CATEGORY, $insert_sql_data);
          /*
            // moved below so evaluated separately from current category
            if (isset($_POST['add_type_all'])) {
            zen_restrict_sub_categories($_POST['categories_id'], $_POST['restrict_type']);
            }
           */
        }
        // add product type restrictions to subcategories if not already set
        if (isset($_POST['add_type_all'])) {
          zen_restrict_sub_categories($_POST['categories_id'], $_POST['restrict_type']);
        }
        $action = "edit";
        zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'action=edit_category&cPath=' . $cPath . '&cID=' . zen_db_prepare_input($_POST['categories_id'])));
      }
      if (isset($_POST['categories_id'])) {
        $categories_id = zen_db_prepare_input($_POST['categories_id']);
      }
      $sort_order = zen_db_prepare_input($_POST['sort_order']);

      $sql_data_array = array('sort_order' => (int)$sort_order);

      if ($action == 'insert_category') {
        $insert_sql_data = array(
          'parent_id' => (int)$current_category_id,
          'date_added' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

        zen_db_perform(TABLE_CATEGORIES, $sql_data_array);

        $categories_id = zen_db_insert_id();
        // check if [arent is restricted
        $sql = "select parent_id
                from " . TABLE_CATEGORIES . "
                where categories_id = '" . (int)$categories_id . "'";

        $parent_cat = $db->Execute($sql);
        if ($parent_cat->fields['parent_id'] != '0') {
          $sql = "SELECT *
                  FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                  WHERE category_id = '" . $parent_cat->fields['parent_id'] . "'";
          $has_type = $db->Execute($sql);
          if ($has_type->RecordCount() > 0) {
            while (!$has_type->EOF) {
              $insert_sql_data = array(
                'category_id' => (int)$categories_id,
                'product_type_id' => (int)$has_type->fields['product_type_id']);
              zen_db_perform(TABLE_PRODUCT_TYPES_TO_CATEGORY, $insert_sql_data);
              $has_type->moveNext();
            }
          }
        }
      } elseif ($action == 'update_category') {
        $update_sql_data = array('last_modified' => 'now()');

        $sql_data_array = array_merge($sql_data_array, $update_sql_data);

        zen_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
      }

      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $categories_name_array = $_POST['categories_name'];
        $categories_description_array = $_POST['categories_description'];
        $language_id = $languages[$i]['id'];

        // clean $categories_description when blank or just <p /> left behind
        $sql_data_array = array(
          'categories_name' => zen_db_prepare_input($categories_name_array[$language_id]),
          'categories_description' => ($categories_description_array[$language_id] == '<p />' ? '' : zen_db_prepare_input($categories_description_array[$language_id])));

        if ($action == 'insert_category') {
          $insert_sql_data = array(
            'categories_id' => (int)$categories_id,
            'language_id' => (int)$languages[$i]['id']);

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
        } elseif ($action == 'update_category') {
          zen_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
        }
      }

      if ($_POST['categories_image_manual'] != '') {
        // add image manually
        $categories_image_name = zen_db_input($_POST['img_dir'] . $_POST['categories_image_manual']);
        $db->Execute("update " . TABLE_CATEGORIES . "
                      set categories_image = '" . $categories_image_name . "'
                      where categories_id = '" . (int)$categories_id . "'");
      } else {
        if ($categories_image = new upload('categories_image')) {
          $categories_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
          $categories_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
          if ($categories_image->parse() && $categories_image->save()) {
            $categories_image_name = zen_db_input($_POST['img_dir'] . $categories_image->filename);
          }
          if ($categories_image->filename != 'none' && $categories_image->filename != '' && $_POST['image_delete'] != 1) {
            // save filename when not set to none and not blank
            $db_filename = zen_limit_image_filename($categories_image_name, TABLE_CATEGORIES, 'categories_image');
            $db->Execute("update " . TABLE_CATEGORIES . "
                          set categories_image = '" . $db_filename . "'
                          where categories_id = '" . (int)$categories_id . "'");
          } else {
            // remove filename when set to none and not blank
            if ($categories_image->filename != '' || $_POST['image_delete'] == 1) {
              $db->Execute("update " . TABLE_CATEGORIES . "
                            set categories_image = ''
                            where categories_id = '" . (int)$categories_id . "'");
            }
          }
        }
      }

      zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $categories_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
      break;

    // bof: categories meta tags
    case 'update_category_meta_tags':
      // add or update meta tags
      $categories_id = $_POST['categories_id'];
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $check = $db->Execute("SELECT *
                               FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                               WHERE categories_id = " . (int)$categories_id . "
                               AND language_id = " . (int)$language_id);
        if ($check->RecordCount() > 0) {
          $action = 'update_category_meta_tags';
        } else {
          $action = 'insert_categories_meta_tags';
        }
        if (empty($_POST['metatags_title'][$language_id]) && empty($_POST['metatags_keywords'][$language_id]) && empty($_POST['metatags_description'][$language_id])) {
          $action = 'delete_category_meta_tags';
        }

        $sql_data_array = array(
          'metatags_title' => zen_db_prepare_input($_POST['metatags_title'][$language_id]),
          'metatags_keywords' => zen_db_prepare_input($_POST['metatags_keywords'][$language_id]),
          'metatags_description' => zen_db_prepare_input($_POST['metatags_description'][$language_id]));

        if ($action == 'insert_categories_meta_tags') {
          $insert_sql_data = array(
            'categories_id' => (int)$categories_id,
            'language_id' => (int)$language_id);
          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_METATAGS_CATEGORIES_DESCRIPTION, $sql_data_array);
        } elseif ($action == 'update_category_meta_tags') {
          zen_db_perform(TABLE_METATAGS_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = " . (int)$categories_id . " and language_id = " . (int)$language_id);
        } elseif ($action == 'delete_category_meta_tags') {
          $remove_categories_metatag = "DELETE FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " WHERE categories_id = " . (int)$categories_id . " AND language_id = " . (int)$language_id;
          $db->Execute($remove_categories_metatag);
        }
      }

      zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $categories_id));
      break;
    // eof: categories meta tags

    case 'new_category':
    case 'edit_category':
    case 'edit_category_meta_tags':
      // handled by another switch/case later
      break;

    default:
      $action = $_GET['action'] = '';
  }
}

// check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
  if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  }
} else {
  $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <?php if ($action != 'edit_category_meta_tags') { // bof: categories meta tags  ?>
      <?php if ($editor_handler != '') include ($editor_handler); ?>
    <?php } // meta tags disable editor eof: categories meta tags ?>
  </head>
  <body onload="init();">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->

    <?php
    // Make an array of product types
    $sql = "select type_id, type_name from " . TABLE_PRODUCT_TYPES;
    $product_types = $db->Execute($sql);
    while (!$product_types->EOF) {
      $type_array[] = array(
        'id' => $product_types->fields['type_id'],
        'text' => $product_types->fields['type_name']
      );
      $product_types->MoveNext();
    }

    if (isset($_GET['cPath'])) {
      $cPath = $_GET['cPath'];
    }
    switch ($action) {
      case 'new_category':
        $formAction = 'insert_category';
        break;
      case 'edit_category':
        $formAction = 'update_category';
        break;
    }
    ?>
    <div class="container-fluid">
        <?php
        if ($action == 'new_category' || $action == 'edit_category') {
          ?>
        <h1><?php echo HEADING_TITLE; ?></h1>
        <?php
        echo zen_draw_form('categories', FILENAME_CATEGORIES, 'action=' . $formAction . '&cPath=' . $cPath . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"');
        echo zen_draw_hidden_field('categories_id', $cInfo->categories_id);
        ?>
        <?php if ($formAction == 'update_category') { ?>
          <div class="form-group">
            <div class="col-sm-12"><?php echo TEXT_EDIT_INTRO; ?></div>
          </div>
        <?php } ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_CATEGORIES_NAME, '', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                ?>
              <div class="input-group">
                <span class="input-group-addon">
                    <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
                </span>
                <?php echo zen_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', htmlspecialchars(zen_get_category_name($cInfo->categories_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name') . ' class="form-control"'); ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CATEGORIES_DESCRIPTION, 'categories_description', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                ?>
              <div class="input-group">
                <span class="input-group-addon">
                    <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
                </span>
                <?php echo zen_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', htmlspecialchars(zen_get_category_description($cInfo->categories_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_CATEGORIES_IMAGE, 'categories_image', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_file_field('categories_image', '', 'class="form-control"'); ?>
          </div>
        </div>
        <?php
        $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
        $default_directory = substr($cInfo->categories_image, 0, strpos($cInfo->categories_image, '/') + 1);
        ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CATEGORIES_IMAGE_DIR, 'img_dir', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CATEGORIES_IMAGE_MANUAL, 'categories_image_manual', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('categories_image_manual', '', 'class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-3">
          </div>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_info_image($cInfo->categories_image, $cInfo->categories_name); ?>
              <?php echo '<br />' . $cInfo->categories_image; ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_IMAGES_DELETE, 'image_delete', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('image_delete', '0', true) . TABLE_HEADING_NO; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('image_delete', '1', false) . TABLE_HEADING_YES; ?></label>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_SORT_ORDER, 'sort_order', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('sort_order', $cInfo->sort_order, 'size="6" class="form-control"'); ?>
          </div>
        </div>
        <div class="floatButton">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button> <a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php
        if ($action == 'edit_category') {
          ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_RESTRICT_PRODUCT_TYPE, 'restrict_type', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_pull_down_menu('restrict_type', $type_array, '', 'class="form-control"'); ?>
              <br>
              <input type="submit" name="add_type_all" class="btn btn-info" value="<?php echo BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_ON; ?>"> <input type="submit" name="add_type" class="btn btn-info" value="<?php echo BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_OFF; ?>">
            </div>
          </div>
          <?php
        }
        ?>
        <?php echo '</form>'; ?>
        <?php
        $restrict_types_query = "SELECT *
                                 FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                                 WHERE category_id = " . (int)$cInfo->categories_id;

        $restrict_types = $db->Execute($restrict_types_query);
        if ($restrict_types->RecordCount() > 0) {
          ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_CATEGORY_HAS_RESTRICTIONS, 'remove_type', 'class="col-sm-3 form-label"'); ?>
            <div class="col-sm-9 col-md-6">
                <?php
                foreach ($restrict_types as $restrict_type) {
                  $type_query = "SELECT type_name
                                 FROM " . TABLE_PRODUCT_TYPES . "
                                 WHERE type_id = " . (int)$restrict_type['product_type_id'];
                  $type = $db->Execute($type_query);
                  ?>
                <div class="form-group">
                    <?php echo zen_draw_form('remove_type', FILENAME_CATEGORIES, 'action=remove_type' . (isset($cPath) ? '&cPath=' . $cPath : '') . '&cID=' . $cInfo->categories_id); ?>
                    <?php echo zen_draw_hidden_field('type_id', $restrict_types->fields['product_type_id']); ?>
                  <button type="submit" class="btn btn-warning"><?php echo IMAGE_DELETE; ?></button>
                  <?php echo '</form>'; ?>
                  <?php echo $type->fields['type_name']; ?>
                </div>
                <?php
              }
              ?>
            </div>
          </div>
          <?php
        }
      } elseif ($action == 'edit_category_meta_tags') {
        ?>
        <h1><?php echo TEXT_INFO_HEADING_EDIT_CATEGORY_META_TAGS; ?></h1>

        <?php echo zen_draw_form('categories', FILENAME_CATEGORIES, 'action=update_category_meta_tags&cPath=' . $cPath, 'post', 'enctype="multipart/form-data" class="form-horizontal"'); ?>
        <?php echo zen_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
        <div class="form-group">
          <?php echo TEXT_EDIT_CATEGORIES_META_TAGS_INTRO; ?> - <strong><?php echo $cInfo->categories_id ?> <?php echo $cInfo->categories_name; ?></strong>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_CATEGORIES_META_TAGS_TITLE, 'metatags_title[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                ?>
              <div class="input-group">
                <span class="input-group-addon">
                    <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['metatags_title']); ?>
                </span>
                <?php echo zen_draw_input_field('metatags_title[' . $languages[$i]['id'] . ']', htmlspecialchars(zen_get_category_metatags_title($cInfo->categories_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_METATAGS_CATEGORIES_DESCRIPTION, 'metatags_title') . ' class="form-control"');
                ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_CATEGORIES_META_TAGS_KEYWORDS, 'metatags_keywords[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                ?>
              <div class="input-group">
                <span class="input-group-addon">
                    <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['metatags_keywords']); ?>
                </span>
                <?php echo zen_draw_textarea_field('metatags_keywords[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', htmlspecialchars(zen_get_category_metatags_keywords($cInfo->categories_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="form-control noEditor"');
                ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_EDIT_CATEGORIES_META_TAGS_DESCRIPTION, 'metatags_description[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                ?>
              <div class="input-group">
                <span class="input-group-addon">
                  <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
                </span>
                <?php echo zen_draw_textarea_field('metatags_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', htmlspecialchars(zen_get_category_metatags_description($cInfo->categories_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="form-control noEditor"');
                ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group text-right">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button> <a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php echo '</form>'; ?>
        <?php
      }
      ?>
    </div>
    <!-- body_text_eof //-->
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
