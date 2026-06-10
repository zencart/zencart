<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\Templates;

use Zencart\Traits\Singleton;

/**
 * @since ZC v3.0.0
 */
class TemplateDto
{
    use Singleton;

    private array $templates = [];

    /**
     * @since ZC v3.0.0
     */
    public function getAllTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getTemplate(string $template_name): ?array
    {
        return $this->templates[$template_name] ?? null;
    }

    /**
     * @since ZC v3.0.0
     */
    public function updateTemplate(string $template_name, array $template_parameters): array
    {
        $this->templates[$template_name] = array_merge($this->templates[$template_name] ?? [], $template_parameters);
        return $this->templates[$template_name];
    }

    /**
     * @since ZC v3.0.0
     */
    public function removeTemplate($template_name): void
    {
        unset($this->templates[$template_name]);
    }
}
