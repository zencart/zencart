<?php
$defs = (array)json_decode($setting['setting_definition'], true);
$default = (isset($setting['setting_value'])) ? $setting['setting_value'] : $setting['initial_value'];
echo zen_draw_pull_down_menu($setting['setting_key'], $defs['options'], $default, '', false);

