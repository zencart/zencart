<?php

/**
 * additional_images module
 *
 * Prepares list of additional product images to be displayed in template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 25 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$GLOBALS['zco_notifier']->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_START');

if (!defined('IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE')) {
    define('IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE', 'Yes');
}

/**
 * Parse the product image (from product record) into its components
 * for use in additional image filesystem lookup
 *
 * @return array{0:string, 1:string, 2:string}
 * @since ZC v2.2.0
 */
function zen_get_image_lookup_filename_components(string $products_image, $for_glob = false, $append_legacy_underscore_to_nonsubdirs = false): array
{
    $image_info = pathinfo($products_image);
    $products_image_extension = '.' . $image_info['extension'];
    $products_image_base = $image_info['filename'];
    $name_including_ext = $image_info['basename'];
    $image_directory = $image_info['dirname'];

    $products_image_directory = DIR_WS_IMAGES;
    // Append "/" to directory if in a subdirectory, and append "_" suffix to base name
    if (!in_array($image_directory, ['.', '/', '..', ''], true)) {
        $products_image_directory = DIR_WS_IMAGES . $image_directory . '/';
        $products_image_base .= '_';
    } else if ($append_legacy_underscore_to_nonsubdirs) {
        $products_image_base .= ($for_glob ? '?' : '_');
    }

    // protect against double-underscores in lookup pattern
    if (str_ends_with($products_image_base, '__')) {
        $products_image_base = substr($products_image_base, 0, -1);
    }

    return [$products_image_base, $products_image_extension, $products_image_directory];
}
/**
 * Scan the filesystem for additional images matching the product's main image
 *
 * @param string $products_image The main product image filename (with path if in subdirectory)
 * @return array{imgs:array, dir:string} Array of additional image filenames (without path) and the directory path
 * @since ZC v2.2.0
 */
function zen_lookup_additional_images_from_filesystem(string $products_image): array
{
    $images_array = [];

    // parse the products_image into its components
    [$products_image_base, $products_image_extension, $products_image_directory] = zen_get_image_lookup_filename_components($products_image, false, defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy');

    // Scan directory for additional matching images using the base name and extension as pattern. Ignoring larger sizes/suffixes at this point.
    $file_extension = $products_image_extension;
    if ($dir = @dir($products_image_directory)) {
        while ($file = $dir->read()) {
            if (!is_dir($products_image_directory . $file)) {
                // -----
                // Some additional-image-display plugins (like Fual Slimbox) have some additional checks to see
                // if the file is "valid"; this notifier "accommodates" that processing, providing these parameters:
                //
                // $p1 ... (r/o) ... An array containing the variables identifying the current image.
                // $p2 ... (r/w) ... A boolean indicator, set to true by any observer to note that the image is "acceptable".
                //
                $current_image_match = false;
                $GLOBALS['zco_notifier']->notify(
                    'NOTIFY_MODULES_ADDITIONAL_IMAGES_FILE_MATCH',
                    [
                        'file' => $file,
                        'file_extension' => $file_extension,
                        'products_image' => $products_image,
                        'products_image_base' => $products_image_base,
                    ],
                    $current_image_match
                );

                // extension match check
                if ($current_image_match || substr($file, strrpos($file, '.')) === $file_extension) {
                    // base name match check
                    if ($current_image_match || preg_match('/' . preg_quote($products_image_base, '/') . '/i', $file) === 1) {
                        // Exclude the main product image itself from the list
                        if ($current_image_match || $file !== $products_image) {
                            // Ensure that the match is in the correct directory
                            if ($products_image_base . str_replace($products_image_base, '', $file) === $file) {
                                //  echo 'I AM A MATCH ' . $file . '<br>';
                                $images_array[] = $file;
                            } else {
                                //  echo 'I AM NOT A MATCH ' . $file . '<br>';
                            }
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
    return ['imgs' => $images_array, 'dir' => $products_image_directory];
}

$modal_images = [];
$images_array = [];

// do not check for additional images when turned off
if ($products_image !== '' && !empty($flag_show_product_info_additional_images)) {

    if (ADDITIONAL_IMAGES_HANDLING === 'Database') {
        $products_image_directory = DIR_WS_IMAGES;
        $images_array = (new Product((int)$_GET['products_id']))->get('additional_images') ?? [];
        $images_array = array_map(static fn($f) => $f['image_filename'], $images_array);
    } else {
        ['imgs' => $images_array, 'dir' => $products_image_directory] = zen_lookup_additional_images_from_filesystem($products_image);
    }
}

$GLOBALS['zco_notifier']->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_LIST', null, $images_array);


// Build output based on images found
$num_images = count($images_array);
$list_box_contents = [];
$title = '';

$max_image_grid_columns = (int)IMAGES_AUTO_ADDED; // "Number of additional-images per row"

if ($num_images > 0) {
    $row = 0;
    $col = 0;
    if ($num_images < $max_image_grid_columns || $max_image_grid_columns === 0) {
        $col_width = floor(100 / $num_images);
    } else {
        $col_width = floor(100 / $max_image_grid_columns);
    }

    for ($i = 0, $n = $num_images; $i < $n; $i++) {
        $file = $images_array[$i];
        $products_image_extension = substr($file, strrpos($file, '.'));
        $products_image_large = str_replace(DIR_WS_IMAGES, DIR_WS_IMAGES . 'large/', $products_image_directory) . str_replace($products_image_extension, '', $file) . IMAGE_SUFFIX_LARGE . $products_image_extension;

        // -----
        // This notifier lets any image-handler know the current image being processed, providing the following parameters:
        //
        // $p1 ... (r/o) ... The current product's name
        // $p2 ... (r/w) ... The (possibly updated) filename (including path) of the current additional image.
        //
        $GLOBALS['zco_notifier']->notify('NOTIFY_MODULES_ADDITIONAL_IMAGES_GET_LARGE', $products_name, $products_image_large);

        $flag_has_large = file_exists($products_image_large);
        $products_image_large = ($flag_has_large ? $products_image_large : $products_image_directory . $file);
        $flag_display_large = (IMAGE_ADDITIONAL_DISPLAY_LINK_EVEN_WHEN_NO_LARGE === 'Yes' || $flag_has_large);
        $base_image = $products_image_directory . $file;
        $thumb_slashes = zen_image(addslashes($base_image), addslashes($products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);

        // -----
        // This notifier lets any image-handler "massage" the name of the current thumbnail image name (with appropriate
        // slashes for javascript/jQuery display):
        //
        // $p1 ... (n/a) ... An empty array, not applicable.
        // $p2 ... (r/w) ... A reference to the "slashed" thumbnail image name.
        //
        $GLOBALS['zco_notifier']->notify('NOTIFY_MODULES_ADDITIONAL_IMAGES_THUMB_SLASHES', [], $thumb_slashes);

        $thumb_regular = zen_image($base_image, $products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT);
        $large_link = zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pic=' . $i . '&products_image_large_additional=' . $products_image_large);

        // Link Preparation:
        // -----
        // This notifier gives notice that an additional image's script link is requested.  A monitoring observer sets
        // the $p2 value to boolean true if it has provided an alternate form of that link; otherwise, the base code will
        // create that value.
        //
        // $p1 ... (r/o) ... An associative array, containing the 'flag_display_large', 'products_name', 'products_image_large', 'thumb_slashes' and current 'index' values.
        // $p2 ... (r/w) ... A reference to the $script_link value, set here to boolean false; if an observer modifies that value, then this module's processing is bypassed.
        // $p3 ... (r/w) ... A reference to the $link_parameters value, which defines the parameters associated with the above
        //                     link's display.  If the $script_link is updated, these parameters will be used for the display.
        //
        $script_link = false;
        $link_parameters = 'class="additionalImages centeredContent back"' . ' ' . 'style="width:' . $col_width . '%;"';
        $GLOBALS['zco_notifier']->notify(
            'NOTIFY_MODULES_ADDITIONAL_IMAGES_SCRIPT_LINK',
            [
                'flag_display_large' => $flag_display_large,
                'products_name' => $products_name,
                'products_image_large' => $products_image_large,
                'thumb_slashes' => $thumb_slashes,
                'large_link' => $large_link,
                'index' => $i,
            ],
            $script_link,
            $link_parameters
        );
        if ($script_link === false) {
            $script_link = '<script>' . "\n" . 'document.write(\'' .
                ($flag_display_large
                    ? '<a href="javascript:popupWindow(\\\'' . str_replace($products_image_large, urlencode(addslashes($products_image_large)), $large_link) . '\\\')">' . $thumb_slashes . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'
                    : $thumb_slashes)
                . '\');' . "\n" . '</script>';
        }

        $noscript_link = '<noscript>' . ($flag_display_large
                ? '<a href="' . zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pic=' . $i . '&products_image_large_additional=' . $products_image_large) . '" target="_blank">' . $thumb_regular . '<br><span class="imgLinkAdditional">' . TEXT_CLICK_TO_ENLARGE . '</span></a>'
                : $thumb_regular)
            . '</noscript>';

        //      $alternate_link = '<a href="' . $products_image_large . '" onclick="javascript:popupWindow(\''. $large_link . '\') return false;" title="' . $products_name . '" target="_blank">' . $thumb_regular . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>';

        $link = $script_link . "\n      " . $noscript_link;
        // $link = $alternate_link;

        // List Box array generation:
        $list_box_contents[$row][$col] = [
            'params' => $link_parameters,
            'text' => "\n      " . $link,
        ];

        $modal_images[] = [
            'image' => $file,
            'products_name' => $products_name,
            'base_image' => $base_image,
            'products_image_large' => $products_image_large,
        ];

        $col++;
        if ($col > ($max_image_grid_columns - 1)) {
            $col = 0;
            $row++;
        }
    } // end for loop
} // endif

$GLOBALS['zco_notifier']->notify('NOTIFY_MODULES_ADDITIONAL_PRODUCT_IMAGES_END');
