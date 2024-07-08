<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v4.5.0
// -----
// Based on the Find Duplicate Models plugin (https://www.zen-cart.com/downloads.php?do=file&id=1323) by swguy.
//
require 'includes/application_top.php';
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <meta charset="<?= CHARSET ?>">
    <title><?= TITLE ?></title>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
.model-number {
    font-weight: bold;
}
.table > tbody > tr.new-model > td,
.table > tbody > tr.table-title > td {
    border-top: 1px solid black;
}
.table > tbody > tr.table-title > td {
    font-size: larger;
    text-transform: uppercase;
    font-weight: bold;
}
.enabled {
    color: green;
    font-weight: bold;
}
.disabled {
    color: red;
    font-weight: bold;
}
    </style>
</head>
<body>
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <div class="container-fluid">
        <h1><?= HEADING_TITLE ?></h1>
        <p><?= INSTRUCTIONS ?></p>
<?php
$include_disabled = (isset($_GET['include_disabled']));
?>
        <?= zen_draw_form('options', FILENAME_POSM_FIND_DUPLICATE_MODELNUMS, '', 'get', 'class="form-inline"') ?>
            <div class="checkbox">
                <label class="control-label">
                    <?= zen_draw_checkbox_field('include_disabled', '', $include_disabled) . ' ' . INCLUDE_DISABLED ?>
                </label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?= BUTTON_GO ?></button>
            </div>
        <?= '</form>' ?>
        <hr>

        <table class="table table-condensed table-hover">
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?= HEADING_PRODUCTS_MODEL ?></th>
                <th class="dataTableHeadingContent"><?= HEADING_POSM_MODEL ?></th>
                <th class="dataTableHeadingContent text-center"><?= HEADING_PRODUCTS_LINK ?></th>
                <th class="dataTableHeadingContent text-center"><?= HEADING_POSM_LINK ?></th>
                <th class="dataTableHeadingContent text-center"><?= HEADING_PRODUCTS_DISABLED ?></th>
                <th class="dataTableHeadingContent"><?= HEADING_PRODUCTS_NAME ?></th>
            </tr>
<?php
// -----
// Gather the list of products_id values associated with 'managed' products. This list will be used multiple
// times to produce the 3-stage report.
//
$managed = $db->Execute(
    "SELECT DISTINCT products_id
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
      ORDER BY products_id ASC"
);
$managed_products = [];
foreach ($managed as $next_product) {
    $managed_products[] = $next_product['products_id'];
}
if (count($managed_products) === 0) {
    $managed_products_exist = false;
} else {
    $managed_products_exist = true;
    $managed_products_list = implode(',', $managed_products);
}
unset($managed, $managed_products);

// -----
// First, report the 'unmanaged' products' duplicate models found for other 'unmanaged' products.
//
// Using 'COALESCE' to 'cast' NULL products_model values to an empty string.
//
if ($managed_products_exist === true) {
    $where_clause = " WHERE p.products_id NOT IN ($managed_products_list)";
    $join_clause = " WHERE products_id NOT IN ($managed_products_list)";
    if ($include_disabled === false) {
        $where_clause .= ' AND p.products_status = 1';
        $join_clause .= ' AND products_status = 1';
    }
} elseif ($include_disabled === true) {
    $where_clause = '';
    $join_clause = '';
} else {
    $where_clause = ' WHERE p.products_status = 1';
    $join_clause = ' WHERE products_status = 1';
}

$dup_model_query1 =
    "SELECT DISTINCT p.products_id, p.products_model, pd.products_name, p.products_type, p.products_status
       FROM " . TABLE_PRODUCTS . " p
            INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                ON pd.products_id = p.products_id
               AND pd.language_id = " . $_SESSION['languages_id'] . "
            INNER JOIN (
                SELECT DISTINCT products_id, COALESCE(products_model, '') AS dup_model
                  FROM " . TABLE_PRODUCTS . "
                $join_clause
            ) AS dups ON dups.dup_model = COALESCE(p.products_model, '')
       $where_clause
       GROUP BY p.products_id, p.products_model, pd.products_name, p.products_type, p.products_status
      HAVING (COUNT(p.products_model) > 1)
    ORDER BY p.products_model, pd.products_name, p.products_id";
$dup_models = $db->Execute($dup_model_query1);
?>
            <tr class="dataTableHeadingRow table-title">
                <td colspan="6" class="dataTableHeadingContent text-center"><?= DUPS_UNMANAGED_UNMANAGED ?></td>
            </tr>
<?php
if ($dup_models->EOF) {
?>
            <tr>
                <td colspan="6" class="text-center"><b><?= NO_DUPS_FOUND ?></b></td>
            </tr>
<?php
} else {
    $current_model = false;
    $posm_model = '---';
    $posm_link = '---';
    foreach ($dup_models as $next_dup) {
        $products_id = $next_dup['products_id'];
        $products_model = strtoupper($next_dup['products_model']);

        if ($current_model === false || $current_model !== $products_model) {
            $current_model = $products_model;
            $products_model = !empty($products_model) ? $products_model : POSM_MODEL_IS_EMPTY;
            $row_class = ' class="new-model"';
        } else {
            $products_model = '&nbsp;';
            $row_class = '';
        }
        if ($next_dup['products_status'] === '0') {
            $products_status = '<span class="disabled">&cross;</span>';
        } else {
            $products_status = '<span class="enabled">&check;</span>';
        }
        $products_link = zen_href_link(FILENAME_PRODUCT, 'product_type=' . $next_dup['products_type'] . "&action=new_product&pID=$products_id");
?>
            <tr<?= $row_class;?>>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($products_model) ?></td>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($posm_model) ?></td>
                <td class="dataTableContent text-center"><a href="<?= $products_link ?>"><?= $products_id ?></a></td>
                <td class="dataTableContent text-center"><?= $posm_link ?></td>
                <td class="dataTableContent text-center"><?= $products_status ?></td>
                <td class="dataTableContent"><?= zen_output_string_protected($next_dup['products_name']) ?></td>
            </tr>
<?php
    }
}

// -----
// Next, report the 'unmanaged' products' with models duplicated in 'managed' products.
//
if ($managed_products_exist === true) {
    $where_clause = " WHERE p.products_id NOT IN ($managed_products_list)";
    $join_clause = " WHERE pos1.products_id IN ($managed_products_list)";
    if ($include_disabled === false) {
        $where_clause .= ' AND p.products_status = 1';
        $join_clause = ' INNER JOIN ' . TABLE_PRODUCTS . ' p1 ON p1.products_id = pos1.products_id' . $join_clause . ' AND p1.products_status = 1';
    }

    $dup_model_query2 =
        "SELECT p.products_id, p.products_model, pd.products_name, p.products_type, p.master_categories_id, p.products_status, dups.dup_model AS posm_model
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = p.products_id
                   AND pd.language_id = " . $_SESSION['languages_id'] . "
                INNER JOIN (
                    SELECT DISTINCT pos1.products_id, COALESCE(pos1.pos_model, '') AS dup_model
                      FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos1
                    $join_clause
                ) AS dups ON dups.dup_model = COALESCE(p.products_model, '')
           $where_clause
        GROUP BY p.products_id, p.products_model, pd.products_name, p.products_type, p.master_categories_id, p.products_status, posm_model
         HAVING (COUNT(p.products_model) > 1)
        ORDER BY p.products_model, pd.products_name, p.products_id";
    $dup_models = $db->Execute($dup_model_query2);
}
?>
            <tr class="dataTableHeadingRow table-title">
                <td colspan="6" class="dataTableHeadingContent text-center"><?= DUPS_UNMANAGED_MANAGED ?></td>
            </tr>
<?php
if ($managed_products_exist === false || $dup_models->EOF) {
?>
            <tr class="new-model">
                <td colspan="6" class="text-center"><b><?= NO_DUPS_FOUND ?></b></td>
            </tr>
<?php
} else {
    $current_model = false;
    foreach ($dup_models as $next_dup) {
        $products_id = $next_dup['products_id'];
        $products_model = strtoupper($next_dup['products_model']);
        $posm_model = $next_dup['posm_model'];

        if ($current_model === false || $current_model !== $products_model) {
            $current_model = $products_model;
            $products_model = !empty($products_model) ? $products_model : POSM_MODEL_IS_EMPTY;
            $row_class = ' class="new-model"';
        } else {
            $products_model = '&nbsp;';
            $row_class = '';
        }
        if ($next_dup['products_status'] === '0') {
            $products_status = '<span class="disabled">&cross;</span>';
        } else {
            $products_status = '<span class="enabled">&check;</span>';
        }
        $products_link = zen_href_link(FILENAME_PRODUCT, 'product_type=' . $next_dup['products_type'] . "&action=new_product&pID=$products_id");
        $posm_link = zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, "pID=$products_id&category_id=" . $next_dup['master_categories_id']);
?>
            <tr<?= $row_class;?>>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($products_model) ?></td>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($posm_model) ?></td>
                <td class="dataTableContent text-center"><a href="<?= $products_link ?>"><?= $products_id ?></a></td>
                <td class="dataTableContent text-center"><a href="<?= $posm_link ?>"><?= $products_id ?></a></td>
                <td class="dataTableContent text-center"><?= $products_status ?></td>
                <td class="dataTableContent"><?= zen_output_string_protected($next_dup['products_name']) ?></td>
            </tr>
<?php
    }
}

// -----
// Next, report the 'managed' products' with models duplicated in 'managed' products.
//
if ($managed_products_exist === true) {
    $where_clause = " WHERE p.products_id IN ($managed_products_list)";
    $join_clause = " WHERE pos1.products_id IN ($managed_products_list)";
    if ($include_disabled === false) {
        $where_clause .= ' AND p.products_status = 1';
        $join_clause = ' INNER JOIN ' . TABLE_PRODUCTS . ' p1 ON p1.products_id = pos1.products_id' . $join_clause . ' AND p1.products_status = 1';
    }

    $dup_model_query3 =
        "SELECT DISTINCT p.products_id, pd.products_name, p.products_type, p.master_categories_id, p.products_status, dups.dup_model
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = p.products_id
                   AND pd.language_id = " . $_SESSION['languages_id'] . "
                INNER JOIN " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
                    ON pos.products_id = p.products_id
                INNER JOIN (
                    SELECT DISTINCT pos1.products_id, COALESCE(pos1.pos_model, '') AS dup_model
                      FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos1
                     $join_clause
                ) AS dups ON dups.dup_model = COALESCE(pos.pos_model, '')
           $where_clause
        GROUP BY p.products_id, pd.products_name, p.products_type, p.master_categories_id, p.products_status, dups.dup_model
         HAVING (COUNT(dups.dup_model) > 1)
        ORDER BY dups.dup_model, pd.products_name, p.products_id";
    $dup_models = $db->Execute($dup_model_query3);
}
?>
            <tr class="dataTableHeadingRow table-title">
                <td colspan="6" class="dataTableHeadingContent text-center"><?= DUPS_MANAGED_MANAGED ?></td>
            </tr>
<?php
if ($managed_products_exist === false || $dup_models->EOF) {
?>
            <tr class="new-model">
                <td colspan="6" class="text-center"><b><?= NO_DUPS_FOUND ?></b></td>
            </tr>
<?php
} else {
    $current_model = false;
    $products_model = '---';
    foreach ($dup_models as $next_dup) {
        $products_id = $next_dup['products_id'];
        $posm_model = strtoupper($next_dup['dup_model']);

        if ($current_model === false || $current_model !== $posm_model) {
            $current_model = $posm_model;
            $posm_model = !empty($posm_model) ? $posm_model : POSM_MODEL_IS_EMPTY;
            $row_class = ' class="new-model"';
        } else {
            $posm_model = '&nbsp;';
            $row_class = '';
        }
        if ($next_dup['products_status'] === '0') {
            $products_status = '<span class="disabled">&cross;</span>';
        } else {
            $products_status = '<span class="enabled">&check;</span>';
        }
        $products_link = zen_href_link(FILENAME_PRODUCT, 'product_type=' . $next_dup['products_type'] . "&action=new_product&pID=$products_id");
        $posm_link = zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, "pID=$products_id&category_id=" . $next_dup['master_categories_id']);
?>
            <tr<?= $row_class;?>>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($products_model) ?></td>
                <td class="dataTableContent model-number"><?= zen_output_string_protected($posm_model) ?></td>
                <td class="dataTableContent text-center"><a href="<?= $products_link ?>"><?= $products_id ?></a></td>
                <td class="dataTableContent text-center"><a href="<?= $posm_link ?>"><?= $products_id ?></a></td>
                <td class="dataTableContent text-center"><?= $products_status ?></td>
                <td class="dataTableContent"><?= zen_output_string_protected($next_dup['products_name']) ?></td>
            </tr>
<?php
    }
}

?>
        </table>
    </div>
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
