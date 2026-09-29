<?php

declare(strict_types=1);
/**
 * template_func Class.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * template_func Class.
 * This class is used to for template-override calculations
 *
 * @since ZC v1.0.3
 */
class template_func
{
    private string $templateKey;

    /**
     * @since ZC 3.0.0
     */
    public function __construct(string $templateKey)
    {
        $this->templateKey = $templateKey;
    }

    /**
     * Returns an array of filenames matching $template_part from the inheritance-chain for the currently-active
     * template.
     *
     * @since ZC v1.0.3
     */
    public function get_template_part(string $page_directory, string $template_part, string $file_extension = '.php', bool $includeDefaultDirs = true): array
    {
        $pageLoader = Zencart\PageLoader\PageLoader::getInstance();
        return $pageLoader->getTemplatePart($page_directory, $template_part, $file_extension, $includeDefaultDirs);
    }

    /**
     * @since ZC v1.0.3
     */
    public function get_template_dir(string $template_code, string $current_template, string $current_page, string $template_dir, bool $includeDefaultDirs = true): string
    {
        $pageLoader = Zencart\PageLoader\PageLoader::getInstance();
        return $pageLoader->getTemplateDirectory($template_code, $current_template, $current_page, $template_dir, $includeDefaultDirs);
    }

    /**
     * Returns an array of **all** files (including directories) matching $template_part (a regex string) from
     * the inheritance-chain for the currently-active template's $templateSubDir. Unlike the get_template_part method,
     * this method unconditionally doesn't include any files from the default/template_default templates' subdirectories.
     *
     * For example, getTemplateFilesWithDir('^style.*\.css', 'index', 'css') could return an array containing
     *  - zc_plugins/ResponsiveClassicPlugin/v1.0.0/catalog/includes/templates/responsive_classic_plugin/css/stylesheet.css
     *  - zc_plugins/ResponsiveClassicChild/v1.0.0/catalog/includes/templates/responsive_classic_child/css/stylesheet.css
     *
     * @since ZC v3.0.0
     */
    public function getTemplateFilesWithDir(string $template_part, string $current_page, string $templateSubDir): array
    {
        $pageLoader = Zencart\PageLoader\PageLoader::getInstance();
        return $pageLoader->getTemplateFilesWithDir($this->templateKey, $template_part, $current_page, $templateSubDir);
    }
}
