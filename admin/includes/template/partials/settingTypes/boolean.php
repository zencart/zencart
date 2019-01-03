<?php
$default = (isset($setting['setting_value'])) ? $setting['setting_value'] : $setting['initial_value'];
echo zen_draw_checkbox_field($setting['setting_key'], 'on', ($default == 'on'), false);

