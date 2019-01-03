Database Tables
===============

- dashboard_widgets_settings
- dashboard_widgets_settings_to_widget
- dashboard_widgets_settings_to_user


dashboard_widgets_settings
--------------------------

::

    CREATE TABLE dashboard_widgets_settings (
      setting_key varchar(64) NOT NULL,
      setting_name varchar(255) NOT NULL,
      setting_definition longtext NOT NULL,
      setting_type varchar(32) NOT NULL,
      PRIMARY KEY (setting_key)
    ) ENGINE=MyISAM;

Used to store the various types of settings that are available for widgets to use.

-  ``setting_key`` a unique name for the setting e.g. refresh-rate
-  ``setting_name`` a language define key used for the label when displaying the input
-  ``setting_definition`` a json description of the options used for the setting
-  ``setting_type`` the type name of the setting


dashboard_widgets_settings_to_widget
------------------------------------

::

    CREATE TABLE dashboard_widgets_settings_to_widget (
      setting_key varchar(64) NOT NULL,
      widget_key varchar(64) NOT NULL,
      initial_value longtext,
      PRIMARY KEY (setting_key,widget_key)
    ) ENGINE=MyISAM;

Used to associate dashboard_widgets_settings to a widget.

-  ``setting_key`` foreign key reference to dashboard_widgets_settings table
-  ``widget_key`` foreign key reference to dashboard_widgets table
-  ``initial_value`` the initial value to be used


dashboard_widgets_settings_to_user
----------------------------------

::

    CREATE TABLE dashboard_widgets_settings_to_user (
      setting_key varchar(64) NOT NULL,
      widget_key varchar(64) NOT NULL,
      admin_id int(10) unsigned NOT NULL,
      setting_value longtext,
      PRIMARY KEY (setting_key,widget_key,admin_id)
    ) ENGINE=MyISAM;

Used to store settings for widgets per admin user.

-  ``setting_key`` foreign key reference to dashboard_widgets_settings table
-  ``widget_key`` foreign key reference to dashboard_widgets table
-  ``admin_id`` integer id of admin user
-  ``setting_value`` the value for this setting stored for the user
