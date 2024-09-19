<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Aug 01 New in v1.5.8-alpha2 $
 */
if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

/**
 * This observer class enables the 'product_music' handling to make use
 * of the 'base' admin processes for copying, removing, adding and updating
 * a product by attaching to notifications that are pertinent to those
 * processes.
 *
 */
class zcObserverProductMusicObserver extends base
{
    protected $productMusicTypeId = null;

    public function __construct()
    {
        $this->attach(
            $this,
            [
                /* Issued by /includes/functions/functions_products.php */
                'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT',

                /* Issued by /admin/includes/modules/copy_product.php */
                'NOTIFY_ADMIN_PRODUCT_COPY_TO_ATTRIBUTES', 

                /* Issued by /admin/includes/modules/copy_product_confirm.php */
                'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE',

                /* Issued by .admin/includes/modules/update_product.php */
                'NOTIFY_MODULES_UPDATE_PRODUCT_START', 
                'NOTIFY_MODULES_UPDATE_PRODUCT_END', 
            ]
        );
    }

    public function update(&$class, $eventID, $p1, &$p2, &$p3, &$p4)
    {
        switch ($eventID) {

            // -----
            // Issued by /includes/functions/functions_products.php at the end of the
            // product removal process.  Remove all record of the product from the
            // product-music-specific tables, too.
            //
            // On entry:
            //
            // $p2 ... (r/w) A reference to the $product_id to be removed.
            //
            case 'NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT':
                global $db;

                $product_id = $p2;
                $db->Execute(
                    "DELETE FROM " . TABLE_MEDIA_TO_PRODUCTS . "
                      WHERE product_id = " . $product_id
                );
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                      WHERE products_id = " . $product_id
                );
                break;

            // -----
            // Issued by /admin/includes/modules/copy_product.php, at the end of the
            // base copy-options content.  Add a checkbox field to the sidebox
            // content, to see if the media should be copied, too.
            //
            // On entry:
            //
            // $p1 ... (r/o) Contains a copy of the current $pInfo object, containing the products_id.
            // $p2 ... (r/w) Contains a reference to the current sidebox $contents.
            //
            case 'NOTIFY_ADMIN_PRODUCT_COPY_TO_ATTRIBUTES':
                if ($this->isProductMusicProduct($p1->products_id) === true) {
                    $p2[] = [
                        'text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('copy_media', true, true) . TEXT_COPY_MEDIA_MANAGER . '</label></div>'
                    ];
                }
                break;

            // -----
            // Issued by /admin/includes/modules/copy_product_confirm.php at the end
            // of the base product-fields' copy.  For the product_music type, also
            // copy the additional table fields.
            //
            // On entry:
            //
            // $p1 ... (r/o) An associative array containing the base 'products_id' and
            //               the 'dup_products_id' for the copied product.
            //
            case 'NOTIFY_MODULES_COPY_TO_CONFIRM_DUPLICATE':
                global $db;

                $products_id = (int)$p1['products_id'];
                if ($this->isProductMusicProduct($products_id) === false) {
                    return;
                }

                $dup_products_id = $p1['dup_products_id'];
                if (!empty($_POST['copy_media'])) {
                    $product_media = $db->Execute(
                        "SELECT media_id
                           FROM " . TABLE_MEDIA_TO_PRODUCTS . "
                          WHERE product_id = " . $products_id
                    );
                    foreach ($product_media as $item) {
                        $db->Execute(
                            "INSERT INTO " . TABLE_MEDIA_TO_PRODUCTS . "
                                (media_id, product_id)
                             VALUES
                                (" . $item['media_id'] . ", " . $dup_products_id . ")"
                        );
                    }
                }

                $music_extra = $db->Execute(
                    "SELECT artists_id, record_company_id, music_genre_id
                       FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                      WHERE products_id = " . $products_id
                );
                if (!$music_extra->EOF) {
                    $db->Execute(
                        "INSERT INTO " . TABLE_PRODUCT_MUSIC_EXTRA . "
                            (products_id, artists_id, record_company_id, music_genre_id)
                         VALUES
                            (" . 
                                $dup_products_id . ', ' .
                                $music_extra->fields['artists_id'] . ', ' .
                                $music_extra->fields['record_company_id'] . ', ' .
                                $music_extra->fields['music_genre_id'] .
                            ")"
                    );
                }
                break;

            // -----
            // Issued by /admin/includes/modules/update_product.php near the start of
            // its processing.  A product_music type doesn't 'gather' a manufacturers_id,
            // so set its associated POST to indicate that there's no associated manufacturer.
            //
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_START':
                if ($this->isProductMusicProduct($p1['products_id']) === true) {
                    $_POST['manufacturers_id'] = 0;
                }
                break;

            // -----
            // Issued by /admin/includes/modules/update_product.php at the end
            // of the base product-fields' update.  For the product_music type, also
            // save the additional table fields.
            //
            // On entry:
            //
            // $p1 ... (r/o) An associative array containing the product's 'products_id' and
            //               the 'action' being performed.
            //
            case 'NOTIFY_MODULES_UPDATE_PRODUCT_END':
                global $db;

                $products_id = $p1['products_id'];
                if ($this->isProductMusicProduct($p1['products_id']) === false) {
                    return;
                }

                $action = $p1['action'];
                if ($action === 'insert_product') {
                    $sql_data_array = [
                        'products_id' => $products_id,
                        'artists_id' => (int)$_POST['artists_id'],
                        'record_company_id' => (int)$_POST['record_company_id'],
                        'music_genre_id' => (int)$_POST['music_genre_id'],
                    ];
                    zen_db_perform(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array);
                } elseif ($action === 'update_product') {
                    $sql_data_array = [
                        'artists_id' => (int)$_POST['artists_id'],
                        'record_company_id' => (int)$_POST['record_company_id'],
                        'music_genre_id' => (int)$_POST['music_genre_id'],
                    ];
                    zen_db_perform(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array, 'update', 'products_id = ' . $products_id);
                }
                break;

            default:
                break;
        }
    }

    protected function isProductMusicProduct($products_id)
    {
        global $db;

        if ($this->productMusicTypeId === null) {
            $check = $db->Execute(
                "SELECT type_id
                   FROM " . TABLE_PRODUCT_TYPES . "
                  WHERE type_handler = 'product_music'
                  LIMIT 1"
            );
            $this->productMusicTypeId = ($check->EOF) ? 0 : (int)$check->fields['type_id'];
        }
        return (zen_get_products_type($products_id) === $this->productMusicTypeId);
    }
}
