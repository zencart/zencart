<?php
/**
 * Functions related to product images
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 10 New in v2.2.0 $
 */


/**
 * Parse the product image (from product record) into its components
 * for use in product-additional-images lookups from the filesystem.
 *
 * @return array{0:string, 1:string, 2:string}
 * @since ZC v2.2.0 (logic was previously in modules/additional_images.php since v1.3.0)
 */
function zen_get_image_lookup_filename_components(string $products_image, bool $for_glob = false, bool $append_legacy_underscore_to_nonsubdirs = false): array
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
    } elseif ($append_legacy_underscore_to_nonsubdirs) {
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
 * @since ZC v2.2.0 (logic was previously in modules/additional_images.php since v1.3.0)
 */
function zen_lookup_additional_images_from_filesystem(string $products_image): array
{
    $images_array = [];

    // parse the products_image into its components
    [$products_image_base, $file_extension, $products_image_directory] = zen_get_image_lookup_filename_components($products_image, false, defined('ADDITIONAL_IMAGES_MODE') && ADDITIONAL_IMAGES_MODE !== 'legacy');

    // Scan directory for additional matching images using the base name and extension as pattern. Ignoring larger sizes/suffixes at this point.
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
                if ($current_image_match || str_ends_with($file, $file_extension)) {
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

