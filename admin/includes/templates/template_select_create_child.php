<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 25 New in v3.0.0 $
 *
 * @var string $parent_template_directory
 * @var string $plugin_name
 * @var string $template_directory
 * @var string $template_key
 * @var string $template_name
 * @var ?array $has_error
 * @var Zencart\Templates\TemplateSelect $templateSelect
 */
?>
<div class="container-fluid">
    <h1 class="text-center"><?= HEADING_TITLE . HEADING_SUBTITLE_CREATE_CHILD ?></h1>
    <div class="h3 text-center"><?= sprintf(TEXT_INFO_CREATE_CHILD, $parent_template_directory) ?></div>
    <div class="panel panel-default">
        <details class="ts-details">
            <summary class="panel-heading text-center text-primary"><b><?= TEXT_INFO_CREATE_CHILD_SUMMARY ?></b></summary>
            <div class="panel-body"><?= sprintf(TEXT_INFO_CREATE_CHILD_DETAILS, $template_key_max_len, $plugin_name_max_len, $template_directory_max_len) ?></div>
        </details>
    </div>

    <?= zen_draw_form('create', FILENAME_TEMPLATE_SELECT, 'action=previewchild', 'post', 'class="form-horizontal"') ?>
    <?= zen_draw_hidden_field('tKey', $parent_template_directory) ?>
    <?= zen_hide_session_id() ?>
    <div class="table-responsive">
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
                    <td class="dataTableContent text-center">
                        <div class="<?= $has_error['template_key'] ?? '' ?>">
                            <?= zen_draw_input_field(
                                'template_key',
                                zen_output_string_protected($template_key),
                                'class="form-control" id="template-key" maxlength="' . $template_key_max_len . '"'
                            ) ?>
                        </div>
                    </td>
                </tr>

                <tr class="dataTableRow">
                    <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_PLUGIN_NAME, ' :') ?></td>
                    <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_plugin_name) ?></td>
                    <td class="dataTableContent text-center">
                        <div class="<?= $has_error['plugin_name'] ?? '' ?>">
                            <?= zen_draw_input_field(
                                'plugin_name',
                                zen_output_string_protected($plugin_name),
                                'class="form-control" id="plugin-name" maxlength="' . $plugin_name_max_len . '"'
                            ) ?>
                        </div>
                    </td>
                </tr>

                <tr class="dataTableRow">
                    <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_TEMPLATE_NAME, ' :') ?></td>
                    <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_template_name) ?></td>
                    <td class="dataTableContent text-center">
                        <div class="<?= $has_error['template_name'] ?? '' ?>">
                            <?= zen_draw_input_field(
                                'template_name',
                                zen_output_string_protected($template_name),
                                'class="form-control" id="template-name"'
                            ) ?>
                        </div>
                    </td>
                </tr>

                <tr class="dataTableRow">
                    <td class="dataTableContent h6 text-center"><?= rtrim(TEXT_INFO_TEMPLATE_DIRECTORY, ' :') ?></td>
                    <td class="dataTableContent text-center"><?= zen_output_string_protected($parent_template_directory) ?></td>
                    <td class="dataTableContent text-center">
                        <div class="<?= $has_error['template_directory'] ?? '' ?>">
                            <?= zen_draw_input_field(
                                'template_directory',
                                zen_output_string_protected($template_directory),
                                'class="form-control" id="template-dir" maxlength="' . $template_directory_max_len . '"'
                            ) ?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row text-center">
        <button type="submit" class="btn btn-primary me-2 mt-1">
            <?= IMAGE_PREVIEW ?>
        </button>
        <a href="<?= zen_href_link(FILENAME_TEMPLATE_SELECT) ?>" class="btn btn-default mt-1">
            <?= IMAGE_CANCEL ?>
        </a>
    </div>
    <?= '</form>' ?>
</div>
