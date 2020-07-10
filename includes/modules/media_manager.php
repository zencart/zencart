<?php
/**
 * iterates thru media collections/clips
 *
 * @package productTypes
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:28:50 2018 -0500 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * get list of media collections assigned to specified product
 */
$zv_collection_query = "select media_id, product_id from " . TABLE_MEDIA_TO_PRODUCTS . "
                        where product_id = '" . (int)$_GET['products_id'] . "'";
$zq_collections = $db->Execute($zv_collection_query);
$zv_product_has_media = false;
/**
 * loop thru collections to identify actual media clips
 */
if ($zq_collections->RecordCount() > 0) {
  $zv_product_has_media = true;
  while (!$zq_collections->EOF) {
    /**
     * get names of assigned media collections
     */
    $zf_media_manager_query = "select media_id, media_name from " . TABLE_MEDIA_MANAGER . "
                               where media_id = '" . (int)$zq_collections->fields['media_id'] . "'";
    $zq_media_manager = $db->Execute($zf_media_manager_query);
    if ($zq_media_manager->RecordCount() < 1) {
      $zv_product_has_media = false;
    } else {
      /**
       * build array of [collection_id][text] = collection-name
       */
      $za_media_manager[$zq_media_manager->fields['media_id']] = array('text' => $zq_media_manager->fields['media_name']);
      /**
       * get list of media clips associated with the current media collection, sorted by filename (to allow display sort order to be controlled by filename)
       */
      $zv_clips_query = "select media_id, clip_id, clip_filename, clip_type from " . TABLE_MEDIA_CLIPS . "
                         where media_id = '" . (int)$zq_media_manager->fields['media_id'] . "' order by clip_filename";
      $zq_clips = $db->Execute($zv_clips_query);
      if ($zq_clips->RecordCount() < 1) {
        $zv_product_has_media = false;
      } else {
        while (!$zq_clips->EOF) {
          /**
           * get list of media types and filenames associated with the current media
           * @TODO - run this as separate static array, since only needs to run once, not repeatedly in a loop
           */
          $zf_clip_type_query = "select type_ext, type_name from " . TABLE_MEDIA_TYPES . "
                                 where type_id = '" . (int)$zq_clips->fields['clip_type'] . "'";

          $zq_clip_type = $db->Execute($zf_clip_type_query);

          $za_media_manager[$zq_media_manager->fields['media_id']]['clips'][$zq_clips->fields['clip_id']] =
                array('clip_filename' => $zq_clips->fields['clip_filename'],
                      'clip_type' => $zq_clip_type->fields['type_name']);
          $zq_clips->MoveNext();
        }
      }
    }
    $zq_collections->MoveNext();
  }
}
$zv_product_has_media = (count($za_media_manager) > 0);
