<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: media_manager.php 4873 2006-11-02 09:12:46Z drbyte $
//

define('HEADING_TITLE_MEDIA_MANAGER', 'Media Manager');

define('TABLE_HEADING_MEDIA', 'Collection Name');
define('TABLE_HEADING_ACTION', 'Action');
define('TEXT_HEADING_NEW_MEDIA_COLLECTION', 'New Media Collection');
define('TEXT_NEW_INTRO', 'Please enter the details of the new media collection below');
define('TEXT_MEDIA_COLLECTION_NAME', 'Media Collection Name');
define('TEXT_MEDIA_EDIT_INSTRUCTIONS', 'Use the section above to change the Media Collection Name, then clicking on the save button.<br /><br />
                                        Use the selection below to add or remove media clips from the media collection.');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_PRODUCTS', 'Linked Products:');
define('TEXT_CLIPS', 'Linked Clips:');
define('TEXT_NO_PRODUCTS', 'No Products in this category');
define('TEXT_HEADING_EDIT_MEDIA_COLLECTION', 'Edit Media Collection');
define('TEXT_EDIT_INTRO', 'Please amend the details of the new media collection below');
define('TEXT_HEADING_DELETE_MEDIA_COLLECTION', 'Delete Media Collection');
define('TEXT_DELETE_INTRO', 'Do you want to delete this media collection?');
  define('TEXT_DISPLAY_NUMBER_OF_MEDIA', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> Media Collections)');
define('TEXT_ADD_MEDIA_CLIP', 'Add Media Clip');
define('TEXT_MEDIA_CLIP_DIR', 'Upload to Media Directory');
define('TEXT_MEDIA_CLIP_TYPE', 'Media Clip Type');
define('TEXT_HEADING_ASSIGN_MEDIA_COLLECTION', 'Assign Media Collection to Product');
define('TEXT_PRODUCTS_INTRO', 'You can assign and remove this Media Collection for products using the forms below.');
define('IMAGE_PRODUCTS', 'Assign to Product');
define('TEXT_DELETE_PRODUCTS', 'Delete this Media Collection and all items linked to it?');
define('TEXT_DELETE_WARNING_PRODUCTS', '<strong>WARNING:</strong> There are %s items still linked to this Media Collection!');
define('TEXT_WARNING_FOLDER_UNWRITABLE', 'NOTE: media folder ' . DIR_FS_CATALOG_MEDIA . ' is not writable. Cannot upload files.');

define('ERROR_UNKNOWN_DATA', 'ERROR: Unknown data supplied ... operation cancelled');
define('TEXT_ADD','Add');


?>