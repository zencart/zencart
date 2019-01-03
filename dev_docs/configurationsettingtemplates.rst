Template Partials
=================

A template partial is required for each setting type.
Partials are stored in ``{admin}}/includes/template/partials/settingTypes``

Examples

- Boolean Type - fle named ``boolean.php``

::

    <?php
    $default = (isset($setting['setting_value'])) ? $setting['setting_value'] : 'off';
    echo zen_draw_checkbox_field($setting['setting_key'], 'on', ($default == 'on'), false);

- selectFromArray Type - fle named ``selectFromArray.php``


::

    <?php
    $defs = (array)json_decode($setting['setting_definition'], true);
    $default = (isset($setting['setting_value'])) ? $setting['setting_value'] : $setting['initial_value'];
    echo zen_draw_pull_down_menu($setting['setting_key'], $defs['options'], $default, '', false);

