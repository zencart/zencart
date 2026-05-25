<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\Templates;

/**
 * @since ZC v3.0.0
 */
class TemplateSelect
{
    private array $activeTemplates = [];

    /**
     * @since ZC v3.0.0
     */
    public function __construct()
    {
        global $db;

        $result = $db->Execute(
            "SELECT *
               FROM " . TABLE_TEMPLATE_SELECT
        );
        foreach ($result as $next_template) {
            $this->activeTemplates[$next_template['template_language']] = $next_template;
        }

        $active_template_dir = $this->getActiveTemplateDir();
        if ($active_template_dir !== null) {
            TemplateDto::getInstance()->updateTemplate($active_template_dir, ['is_active' => true]);
        }
    }

    /**
     * @since ZC v3.0.0
     */
    public function getAllActiveTemplates(): array
    {
        return $this->activeTemplates;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getActiveTemplateId(): int
    {
        return (int)$this->getActiveTemplateField('template_id');
    }

    /**
     * @since ZC v3.0.0
     */
    public function getActiveTemplateDir(): ?string
    {
        return $this->getActiveTemplateField('template_dir');
    }

    /**
     * Returns the currently-selected template for the specified language,
     * falling back to the default if no language-specific template is set.
     *
     * @since ZC v3.0.0
     */
    public function getTemplateDirForLanguage(string|int $language_id): string
    {
        return $this->activeTemplates[$language_id]['template_dir'] ?? $this->activeTemplates['0']['template_dir'];
    }

    /**
     * @since ZC v3.0.0
     */
    public function getActiveTemplateSettings(): ?string
    {
        return $this->getActiveTemplateField('template_settings');
    }

    /**
     * @since ZC v3.0.0
     */
    public function registerNewTemplate(string $template_dir, int $language_id): false|int
    {
        if ($template_dir === '' || $language_id < 1 || isset($this->activeTemplates[$language_id])) {
            return false;
        }

        global $db;
        $sql =
            "INSERT INTO " . TABLE_TEMPLATE_SELECT . "
                (template_dir, template_language)
             VALUES
                (:tpl:, :lang:)";
        $sql = $db->bindVars($sql, ':tpl:', $template_dir, 'string');
        $sql = $db->bindVars($sql, ':lang:', $language_id, 'integer');
        $db->Execute($sql);
        $template_id = (int)$db->insert_ID();

        $this->activeTemplates[$language_id] = [
            'template_id' => $template_id,
            'template_dir' => $template_dir,
            'template_language' => $language_id,
            'template_settings' => null,
        ];

        return $template_id;
    }

    /**
     * @since ZC v3.0.0
     */
    public function updateTemplateNameForId(int $id, string $template_dir): void
    {
        if ($template_dir === '' || $id < 0) {
            return;
        }

        global $db;

        $sql =
            "UPDATE " . TABLE_TEMPLATE_SELECT . "
                SET template_dir = :tpl:
              WHERE template_id = :id:";
        $sql = $db->bindVars($sql, ':tpl:', $template_dir, 'string');
        $sql = $db->bindVars($sql, ':id:', $id, 'integer');
        $db->Execute($sql);

        foreach ($this->activeTemplates as $language_id => $template_info) {
            if ($id === (int)$template_info['template_id']) {
                $this->activeTemplates[$language_id]['template_dir'] = $template_dir;
                break;
            }
        }
    }

    /**
     * @since ZC v3.0.0
     */
    public function deregisterTemplateId(int $id): bool
    {
        if ($id < 0) {
            return false;
        }

        foreach ($this->activeTemplates as $language_id => $template_info) {
            if ($id === (int)$template_info['template_id']) {
                if ((int)$template_info['template_language'] === 0) {
                    return false;
                }

                unset($this->activeTemplates[$language_id]);

                global $db;
                $db->Execute(
                    "DELETE FROM " . TABLE_TEMPLATE_SELECT . "
                      WHERE template_id = " . (int)$id
                );

                return true;
            }
        }
        return false;
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUnregisteredTemplateLanguages(): array
    {
        global $db;
        $templates = [];
        $sql =
            "SELECT lng.name as language_name, lng.languages_id as language_id
               FROM " . TABLE_LANGUAGES . " lng
              WHERE lng.languages_id NOT IN (SELECT template_language FROM " . TABLE_TEMPLATE_SELECT . ")";
        $results = $db->Execute($sql);
        foreach ($results as $result) {
            $templates[] = $result;
        }
        return $templates;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function getActiveTemplateField(string $field_name): ?string
    {
        return $this->activeTemplates[$_SESSION['languages_id']][$field_name] ?? $this->activeTemplates['0'][$field_name] ?? null;
    }
}
