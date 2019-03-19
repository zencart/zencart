Using Widget Configuration Settings
===================================

Configuration settings can be referenced within Dashboard Widget Classes.

.. note:: Dashboard widget classes are defined in ``app/library/zencart/DashboardWidgets/src/``


To access the settings within these classes you can reference the ``$this->widgetInfo['config_settings']`` array.

Within this array, you can then access individual settings.

To access the individual settings you need to test for the existence of the ``setting_value`` and if that doses not exist use the ``initial_value``

This is because initially a user may not have set a configuration value, so we also need to look at the `initial_value`


As an example, for the Banner Statistics widget, to access the Banner Id configuration setting, we do

::

    $settings = $this->widgetInfo['config_settings'];
    $bannerId = isset($settings['banner-id']['setting_value']) ? $settings['banner-id']['setting_value'] : $settings['banner-id']['initial_value'];







