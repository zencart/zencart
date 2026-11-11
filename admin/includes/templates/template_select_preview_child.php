<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 25 New in v3.0.0 $
 *
 * @var string $parent_plugin_name
 * @var string $parent_template_directory
 * @var string $parent_template_key
 * @var string $parent_template_name
 * @var string $plugin_name
 * @var string $template_directory
 * @var string $template_key
 * @var string $template_name
 * @var Zencart\Templates\TemplateSelect $templateSelect
 */
?>
<div class="container-fluid">
    <h1 class="text-center"><?= HEADING_TITLE . HEADING_SUBTITLE_PREVIEW_CHILD ?></h1>
    <div class="row text-center"><?= TEXT_INFO_PREVIEW_CHILD ?></div>
    <table class="table table-hover">
        <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent">&nbsp;</th>
                <th class="dataTableHeadingContent h5 text-center"><?= TABLE_HEADING_PARENT_VALUE ?></th>
                <th class="dataTableHeadingContent h5 text-center"><?= TABLE_HEADING_CHILD_VALUE ?></th>
            </tr>
        </thead>
        <tbody>
            <tr class="dataTableRow">
                <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_TEMPLATE_KEY, ' :') ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_template_key) ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($template_key) ?></td>
            </tr>

            <tr class="dataTableRow">
                <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_PLUGIN_NAME, ' :') ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_plugin_name) ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($plugin_name) ?></td>
            </tr>

            <tr class="dataTableRow">
                <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_TEMPLATE_NAME, ' :') ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_template_name) ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($template_name) ?></td>
            </tr>

            <tr class="dataTableRow">
                <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_TEMPLATE_DIRECTORY, ' :') ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_template_directory) ?></td>
                <td class="dataTableContent text-center"><?= zen_output_string_protected($template_directory) ?></td>
            </tr>
        </tbody>
    </table>
    <div class="row text-center">
        <?= zen_draw_form('save', FILENAME_TEMPLATE_SELECT, 'action=savechild', 'post') ?>
            <?= zen_draw_hidden_field('tKey', $parent_template_directory) ?>
            <?= zen_draw_hidden_field('template_key', $template_key) ?>
            <?= zen_draw_hidden_field('plugin_name', $plugin_name) ?>
            <?= zen_draw_hidden_field('template_name', $template_name) ?>
            <?= zen_draw_hidden_field('template_directory', $template_directory) ?>
            <button type="submit" class="btn btn-primary me-2 mt-1">
                <?= IMAGE_SAVE ?>
            </button>
        <?= '</form>' ?>
        <a href="<?= zen_href_link(FILENAME_TEMPLATE_SELECT) ?>" class="btn btn-default mt-1">
            <?= IMAGE_CANCEL ?>
        </a>
    </div>
</div>
