<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsCatalogImages;

use Tests\Support\zcUnitTestCase;

class CatalogImagesTest extends zcUnitTestCase
{
    public function setup(): void
    {
        parent::setup();

        defined('DIR_WS_IMAGES') || define('DIR_WS_IMAGES', 'images/');

        $products_image = 'example.jpg';
        zen_define_default('IMAGES_AUTO_ADDED', 0);

        // Need to load the additional_images module to get the zen_get_image_lookup_parts function
        defined('DIR_FS_CATALOG_MODULES') || define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
        require_once DIR_FS_CATALOG_MODULES . 'additional_images.php';
    }

    /**
     * @dataProvider imagesProvider
     */
    public function testImagesFound($image, $for_glob, $underscore_to_nonsubdirs, $expected): void
    {
        $this->assertEquals($expected, zen_get_image_lookup_filename_components($image, $for_glob, $underscore_to_nonsubdirs));
    }

    public function imagesProvider(): array
    {
        defined('DIR_WS_IMAGES') || define('DIR_WS_IMAGES', 'images/');

        return [
            ['example.jpg', false, false, ['example', '.jpg', DIR_WS_IMAGES]],
            ['example.jpg', false, true, ['example_', '.jpg', DIR_WS_IMAGES]],
            ['example.JPG', false, false, ['example', '.JPG', DIR_WS_IMAGES]],
            ['example.png', false, false, ['example', '.png', DIR_WS_IMAGES]],
            ['example.PNG', false, false, ['example', '.PNG', DIR_WS_IMAGES]],
            ['example.PNG', false, true, ['example_', '.PNG', DIR_WS_IMAGES]],
            ['no_sub_dir.jpg', false, false, ['no_sub_dir', '.jpg', DIR_WS_IMAGES]],
            ['no_sub_dir.jpg', false, true, ['no_sub_dir_', '.jpg', DIR_WS_IMAGES]],
            ['dvd/cartoon/disney.JPG', false, false, ['disney_', '.JPG', DIR_WS_IMAGES . 'dvd/cartoon/']],
            ['dvd/cartoon/disney.JPG', false, true, ['disney_', '.JPG', DIR_WS_IMAGES . 'dvd/cartoon/']],
            ['mfg1/acme1.png', false, false, ['acme1_', '.png', DIR_WS_IMAGES . 'mfg1/']],
            ['mfg1/acme1.png', false, true, ['acme1_', '.png', DIR_WS_IMAGES . 'mfg1/']],

            // glob
            ['example.jpg', true, false, ['example', '.jpg', DIR_WS_IMAGES]],
            ['example.jpg', true, true, ['example?', '.jpg', DIR_WS_IMAGES]],
            ['example.JPG', true, false, ['example', '.JPG', DIR_WS_IMAGES]],
            ['example.png', true, false, ['example', '.png', DIR_WS_IMAGES]],
            ['example.PNG', true, false, ['example', '.PNG', DIR_WS_IMAGES]],
            ['example.PNG', true, true, ['example?', '.PNG', DIR_WS_IMAGES]],
            ['no_sub_dir.jpg', true, false, ['no_sub_dir', '.jpg', DIR_WS_IMAGES]],
            ['no_sub_dir.jpg', true, true, ['no_sub_dir?', '.jpg', DIR_WS_IMAGES]],
            ['dvd/cartoon/disney.JPG', true, false, ['disney_', '.JPG', DIR_WS_IMAGES . 'dvd/cartoon/']],
            ['dvd/cartoon/disney.JPG', true, true, ['disney_', '.JPG', DIR_WS_IMAGES . 'dvd/cartoon/']],
            ['mfg1/acme1.png', true, false, ['acme1_', '.png', DIR_WS_IMAGES . 'mfg1/']],
            ['mfg1/acme1.png', true, true, ['acme1_', '.png', DIR_WS_IMAGES . 'mfg1/']],
        ];
    }

}
