<?php
/**
 * additional_images module
 *
 * Prepares list of additional product images to be displayed in template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: additional_images.php 18697 2011-05-04 14:35:20Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_START');

if (!defined('IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE')) define('IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE','Yes');
if (!defined('IMAGE_ENABLE_LARGER_IMAGE_LINKS')) define('IMAGE_ENABLE_LARGER_IMAGE_LINKS','1');
$images_array = array();

// do not check for additional images when turned off
if ($products_image != '' && $flag_show_product_info_additional_images != 0) {
    // prepare image name
    $products_image_extension = substr($products_image, strrpos($products_image, '.'));
    $products_image_base = str_replace($products_image_extension, '', $products_image);

    // if in a subdirectory
    if (strrpos($products_image, '/')) {
        $products_image_match = substr($products_image, strrpos($products_image, '/')+1);
        //echo 'TEST 1: I match ' . $products_image_match . ' - ' . $file . ' -  base ' . $products_image_base . '<br>';
        $products_image_match = str_replace($products_image_extension, '', $products_image_match) . '_';
        $products_image_base = $products_image_match;
    }

    $products_image_directory = str_replace($products_image, '', substr($products_image, strrpos($products_image, '/')));
    if ($products_image_directory != '') {
        $products_image_directory = DIR_WS_IMAGES . str_replace($products_image_directory, '', $products_image) . "/";
    } else {
        $products_image_directory = DIR_WS_IMAGES;
    }

    // Check for additional matching images
    $file_extension = $products_image_extension;
    $products_image_match_array = array();
    if ($dir = @dir($products_image_directory)) {
        while ($file = $dir->read()) {
            if (!is_dir($products_image_directory . $file)) {
                // -----
                // Some additional-image-display plugins (like Fual Slimbox) have some additional checks to see
                // if the file is "valid"; this notifier "accomodates" that processing, providing these parameters:
                //
                // $p1 ... (r/o) ... An array containing the variables identifying the current image.
                // $p2 ... (r/w) ... A boolean indicator, set to true by any observer to note that the image is "acceptable".
                //
                $current_image_match = false;
                $zco_notifier->notify(
                    'NOTIFY_MODULES_ADDITIONAL_IMAGES_FILE_MATCH',
                    array(
                        'file' => $file,
                        'file_extension' => $file_extension,
                        'products_image' => $products_image,
                        'products_image_base' => $products_image_base
                    ),
                    $current_image_match
                );
 
                if ($current_image_match || preg_match('/\Q' . $products_image_base . '\E/i', $file) == 1) {
                    if ($current_image_match || substr($file, 0, strrpos($file, '.')) != substr($products_image, 0, strrpos($products_image, '.'))) {
                        if ($products_image_base . str_replace($products_image_base, '', $file) == $file) {
                            //  echo 'I AM A MATCH ' . $file . '<br>';
                            $images_array[] = $file;
                        } else {
                            //  echo 'I AM NOT A MATCH ' . $file . '<br>';
                        }
                    }
                }
            }
        }
        if (count($images_array)) {
            sort($images_array);
        }
        $dir->close();
    }
}

$zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_LIST', NULL, $images_array);


// Build output based on images found
$num_images = count($images_array);
$list_box_contents = array();
$title = '';

if ($num_images > 0) {
    $row = 0;
    $col = 0;
    if ($num_images < IMAGES_AUTO_ADDED || IMAGES_AUTO_ADDED == 0 ) {
        $col_width = floor(100/$num_images);
    } else {
        $col_width = floor(100/IMAGES_AUTO_ADDED);
    }

    for ($i=0, $n=$num_images; $i<$n; $i++) {
        $file = $images_array[$i];
        $products_image_large = str_replace(DIR_WS_IMAGES, DIR_WS_IMAGES . 'large/', $products_image_directory) . str_replace($products_image_extension, '', $file) . IMAGE_SUFFIX_LARGE . $products_image_extension;
        
        // -----
        // This notifier lets any image-handler know the current image being processed, providing the following parameters:
        //
        // $p1 ... (r/o) ... The current product's name
        // $p2 ... (r/w) ... The (possibly updated) filename (including path) of the current additional image.
        //
        $zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_IMAGES_GET_LARGE', $products_name, $products_image_large);
        
        $flag_has_large = file_exists($products_image_large);
        $products_image_large = ($flag_has_large ? $products_image_large : $products_image_directory . $file);
        $flag_display_large = (IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE == 'Yes' || $flag_has_large);
        $base_image = $products_image_directory . $file;
        $thumb_slashes = zen_image(addslashes($base_image), addslashes($products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
        // remove additional single quotes from image attributes
        $thumb_slashes = preg_replace("/([^\\\\])'/", '$1\\\'', $thumb_slashes);
        
        // -----
        // This notifier lets any image-handler "massage" the name of the current thumbnail image name (with appropriate
        // slashes for javascript/jQuery display):
        //
        // $p1 ... (n/a) ... An empty array, not applicable.
        // $p2 ... (r/w) ... A reference to the "slashed" thumbnail image name.
        //
        $zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_IMAGES_THUMB_SLASHES', array(), $thumb_slashes);

        $thumb_regular = zen_image($base_image, $products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
        $large_link = zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pos=' . $i . '&img=' . $products_image_large);

        $js_rel = '';
        $js_href = 'javascript:popupImageWindow(\\\'' . str_replace($products_image_large, urlencode(addslashes($products_image_large)), $large_link) . '\\\')';

        $zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_LINKS', $i, $products_image_large, $js_href, $js_rel, $products_name, $large_link, $thumb_slashes);


        // Link Preparation:
        // -----
        // This notifier gives notice that an additional image's script link is requested.  A monitoring observer sets
        // the $p2 value to boolean true if it has provided an alternate form of that link; otherwise, the base code will
        // create that value.
        //
        // $p1 ... (r/o) ... An associative array, containing the 'flag_display_large', 'products_name', 'products_image_large' and 'thumb_slashes' values.
        // $p2 ... (r/w) ... A reference to the $script_link value, set here to boolean false; if an observer modifies that value, the
        //                     this module's processing is bypassed.
        //
        $script_link = false;
        $zco_notifier->notify(
            'NOTIFY_MODULES_ADDITIONAL_IMAGES_SCRIPT_LINK',
            array(
                'flag_display_large' => $flag_display_large,
                'products_name' => $products_name,
                'products_image_large' => $products_image_large,
                'thumb_slashes' => $thumb_slashes
            ),
            $script_link
        );
        if ($script_link === false) {
            $script_link = '<script>' . "\n" . 'document.write(\'' . ($flag_display_large ? '<a href="' . $js_href . '"' . ($js_rel != '' ? ' rel="' . $js_rel . '"' : '') . ' title="' . addslashes($products_name) . '">' . $thumb_slashes . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>' : $thumb_slashes) . '\');' . "\n" . '</script>';
        }

        $noscript_link = '<noscript>' . ($flag_display_large ? '<a href="' . zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pos=' . $i . '&img=' . $products_image_large) . '" target="_blank">' . $thumb_regular . '<br /><span class="imgLinkAdditional">' . TEXT_CLICK_TO_ENLARGE . '</span></a>' : $thumb_regular ) . '</noscript>';

        //      $alternate_link = '<a href="' . $products_image_large . '" onclick="javascript:popupImageWindow(\''. $large_link . '\') return false;" title="' . $products_name . '" target="_blank">' . $thumb_regular . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>';

        $link = $script_link . "\n      " . $noscript_link;
        //      $link = $alternate_link;

        // if "click for larger image" is disabled for "additional" images, show only the thumbnail
        if (IMAGE_ENABLE_LARGER_IMAGE_LINKS == 0 || IMAGE_ENABLE_LARGER_IMAGE_LINKS == 2) {
            $link = $thumb_regular;
        }

        // List Box array generation:
        $list_box_contents[$row][$col] = array(
            'params' => 'class="additionalImages productBox centeredContent"',
            'text' => "\n      " . $link
        );
        $col ++;
        if ($col > (IMAGES_AUTO_ADDED -1)) {
            $col = 0;
            $row++;
        }
    } // end for loop
} // endif

$zco_notifier->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_END');
