<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 4.5.0
//
require 'includes/application_top.php';

if (!defined ('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK')) {
    define('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK', DB_PREFIX . 'products_with_attributes_stock');
}

$convert = false;
$conversion = [];
if ($sniffer->table_exists(TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK)) {
    $convert = (isset($_GET['action']) && $_GET['action'] === 'convert');
    $sba_info = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK);
    $posm_options_types = explode(',', POSM_OPTIONS_TYPES_TO_MANAGE);
    foreach ($sba_info as $sba_next) {
        $products_id = $sba_next['products_id'];
        if (!isset($conversion[$products_id])) {
            $option_info = $db->Execute(
                "SELECT DISTINCT pa.options_id
                   FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po
                  WHERE pa.products_id = $products_id
                    AND pa.options_id = po.products_options_id
                    AND po.products_options_type IN (" . POSM_OPTIONS_TYPES_TO_MANAGE . ")"
            );
            $options_array = [];
            foreach ($option_info as $next_option) {
                $options_array[$next_option['options_id']] = zen_options_name($next_option['options_id']);
            }

            $product_info = $db->Execute(
                "SELECT products_name
                   FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                  WHERE products_id = $products_id
                    AND language_id = " . (int)$_SESSION['languages_id'] . "
                  LIMIT 1"
            );
            $products_name = ($product_info->EOF) ? TEXT_MISSING_PRODUCT : $product_info->fields['products_name'];
            $conversion[$products_id] = [
                'name' => $products_name,
                'all_options' => $options_array,
            ];
        }
        $stock_id = $sba_next['stock_id'];
        $conversion[$products_id][$stock_id] = [
            'qty' => $sba_next['quantity'],
            'model' => $sba_next['customid'],
            'attributes_id' => $sba_next['stock_attributes'],
            'errors' => [],
            'options' => [],
        ];
        $attr_info = $db->Execute(
            "SELECT pa.options_id, pa.options_values_id, po.products_options_type
               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po
              WHERE pa.products_attributes_id IN (" . $sba_next['stock_attributes'] . ")
                AND pa.options_id = po.products_options_id"
        );
        foreach ($attr_info as $next_attr) {
            if (!in_array($next_attr['products_options_type'], $posm_options_types)) {
                $conversion[$products_id][$stock_id]['errors'][] = sprintf(TEXT_UNSUPPORTED_OPTION_TYPE, $next_attr['options_id'], $next_attr['products_options_type']);
            }
            $conversion[$products_id][$stock_id]['options'][$next_attr['options_id']] = $next_attr['options_values_id'];
        }
    }
    unset($sba_info, $attr_info, $option_info);
}

if ($convert === true) {
    $db->Execute('TRUNCATE ' . TABLE_PRODUCTS_OPTIONS_STOCK);
    $db->Execute('TRUNCATE ' . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES);
    $completion_message = MESSAGE_CONVERTED_OK;
    $completion_status = 'success';
    foreach ($conversion as $products_id => $products_details) {
        $options_count = count($products_details['all_options']);
        unset($products_details['name'], $products_details['all_options']);
        foreach ($products_details as $stock_id => $stock_details) {
            if ($options_count === 0 || $options_count !== count($stock_details['options'])) {
                $completion_message = MESSAGE_CONVERTED_MISSING;
                $completion_status = 'error';
            } else {
                $db->Execute(
                    "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                        (products_id, products_quantity, pos_hash, pos_model, last_modified)
                     VALUES
                        ($products_id, " . $stock_details['qty'] . ", '" . generate_pos_option_hash ($products_id, $stock_details['options']) . "', '" . $stock_details['model'] . "', now() )"
                );
                $pos_id = $db->Insert_ID();
                foreach ($stock_details['options'] as $options_id => $options_values_id) {
                    $db->Execute(
                        "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                            (pos_id, products_id, options_id, options_values_id)
                         VALUES
                            ($pos_id, $products_id, $options_id, $options_values_id)"
                    );
                }
            }
        }
    }
    $messageStack->add_session($completion_message, $completion_status);
    zen_redirect(zen_href_link (FILENAME_CONVERT_SBA2POSM));
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <meta charset="<?= CHARSET ?>">
    <title><?= TITLE ?></title>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
.buttonLink, .buttonLink:hover, input.buttonLink {
  background-color:white;
  border:1px solid #003366;
  color:#404040;
  border-radius:6px;
  display:inline-block;
  font-family:Verdana;
  font-size:11px;
  font-weight:bold;
  margin: 2px;
  padding:3px 8px;
  text-decoration:none; }
a.buttonLink:hover { background-color: #dcdcdc; }
input.submit_button:hover { background-color:#599659; border: 1px solid #003d00; color: white; cursor: pointer; }
.missing { color: red; }
.ok { color: green; font-weight: bold; }
.p-name { background-color: #dcdcdc; }
.center { text-align: center; }
.p-info, .p-info th, .p-info td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
    </style>
    <script>
function checkSubmit() {
    return confirm('<?= JS_MESSAGE_ARE_YOU_SURE ?>');
}
    </script>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?= HEADING_TITLE . ' <span class="version">(v' . POSM_CURRENT_VERSION . ' &mdash; Last Updated: ' . POSM_CURRENT_UPDATE_DATE . ')</span>' ?></td>
      </tr>

      <tr>
        <td><?= TEXT_INSTRUCTIONS ?></td>
      </tr>
<?php
if (!$sniffer->table_exists(TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK)) {
?>
      <tr>
        <td class="error"><?php echo ERROR_NO_SBA_TABLE; ?></td>
      </tr>
<?php
} else {
?>
      <tr>
        <td><?= zen_draw_form ('convert', FILENAME_CONVERT_SBA2POSM, 'action=convert') ?>
          <div>
            <p><?= TEXT_FORM_INSTRUCTIONS . '&nbsp;&nbsp;' . zen_image_submit('button_submit.gif', BUTTON_ALT_TEXT, 'onclick="return checkSubmit();"') ?></p>
          </div>
        </form></td>
      </tr>
<?php
    foreach ($conversion as $products_id => $products_details) {
?>
      <tr class="p-name">
        <td><?= "($products_id) " . $products_details['name'] ?></td>
      </tr>
<?php
        $all_options = $products_details['all_options'];
        $count_all_options = count($all_options);
        unset($products_details['name'], $products_details['all_options']);
?>
      <tr>
        <td><table class="p-info">
          <tr class="p-info-head">
            <th><?= TABLE_HEADING_STOCK_ID ?></th>
            <th><?= TABLE_HEADING_QUANTITY ?></th>
            <th><?= TABLE_HEADING_MODEL ?></th>
<?php
        foreach ($all_options as $options_id => $option_name) {
?>
            <th><?= $option_name ?></th>
<?php
        }
?>
            <th><?= TABLE_HEADING_STATUS ?></th>
          </tr>
<?php
        foreach ($products_details as $stock_id => $stock_details) {
?>
          <tr>
            <td><?= $stock_id ?></td>
            <td><?= $stock_details['qty'] ?></td>
            <td><?= ($stock_details['model'] === null) ? '&mdash;' : $stock_details['model'] ?></td>
<?php
            foreach ($all_options as $options_id => $options_name) {
?>
            <td><?= (isset($stock_details['options'][$options_id])) ? zen_values_name($stock_details['options'][$options_id]) : '&mdash;' ?></td>
<?php
            }
?>
            <td>
<?php 
            echo (count($stock_details['options']) !== 0 && $count_all_options === count($stock_details['options'])) ? TEXT_OK : sprintf(TEXT_MISSING_OPTIONS, $stock_details['attributes_id']);
            foreach ($stock_details['errors'] as $current_error) {
                echo '<br>' . $current_error;
            }
?>
            </td>
          </tr>
<?php
        }
?>
        </table></td>
      </tr>
<?php
    }
}
?>
    </table></td>
  </tr>
</table>
<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
