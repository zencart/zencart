<?php
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    private string $configGroupTitle = 'Products\\\' Options\\\' Stock Manager';

    protected function executeInstall()
    {
        if (defined('POSMPW_MODULE_VERSION')) {
            $posmpw_module_version = explode(',', POSMPW_MODULE_VERSION);
            if (version_compare($posmpw_module_version[0], '2.3.1', '<')) {
                $this->errorContainer->addError('error', ZC_PLUGIN_POSM_INSTALL_UPDATE_PW, true);
                return false;
            }
        }
        if ($this->nonEncapsulatedVersionPresent() === true) {
            $this->errorContainer->addError('error', ZC_PLUGIN_POSM_INSTALL_REMOVE_PREVIOUS, true);
            return false;
        }

        // -----
        // First, determine the configuration-group-id and install the settings.
        //
        $cgi = $this->getConfigGroupId(
            $this->configGroupTitle,
            $this->configGroupTitle . ' Settings'
        );

        $sql =
            "INSERT IGNORE INTO " . TABLE_CONFIGURATION . " 
                (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
             VALUES
                ('General: Enable Products\' Options\' Stock Manager?', 'POSM_ENABLE', 'true', 'Enable the <em>Products\' Options\' Stock</em> processing for the storefront?', $cgi, 20, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('General: Duplicate Model Numbers', 'POSM_DUPLICATE_MODELNUMS', 'Allow', 'How should the admin-level tools handle duplicate model numbers?  Choose one of:<ol><li><b>Allow</b> (default): No message, allows saving.</li><li><b>Disallow:</b> Issue message, don\'t allow saving.</li><li><b>Message-Only:</b> Issue message, allow saving.</li></ol>', $cgi, 21, now(), NULL, 'zen_cfg_select_option([\'Allow\', \'Disallow\',\'Message-Only\'],'),

                ('General: Divider Color', 'POSM_DIVIDER_COLOR', '#d4ffbd', 'Enter the background color to be used for the divider in <em>Catalog :: Products\' Options\' Stock Manager.</em>', $cgi, 24, now(), NULL, NULL),

                ('General: Options\' Stock Re-order Level', 'POSM_STOCK_REORDER_LEVEL', '5', 'Enter the low-stock level for products with options\' stock.  This value is used to highlight low-stock options within the <em>Catalog->Manage Options\' Stock</em> tools and, if <em>Configuration->E-mail Options->Send Low Stock Emails</em> is enabled, determines the stock-level at which to send those emails.<br><br><strong>Note:</strong> The value entered must consist of numeric (0-9) characters <em>only</em>.', $cgi, 25, now(), NULL, NULL),

                ('General: Back-in-Stock Date Reminder', 'POSM_BIS_DATE_REMINDER', '0', 'If your store is using <em>POSM</em>\'s back-in-stock labels with availability dates, you might want a reminder when a date is imminent. Identify the number of days <b>prior to</b> any expiration that the notification should be issued; set the value to 0 (the default) if you do not want a reminder.', $cgi, 26, now(), NULL, NULL),

                ('General: Option Types to Manage?', 'POSM_OPTIONS_TYPES_TO_MANAGE', '0,2', 'Enter the types of options to manage using a packed, comma-separated list.  Currently, only Dropdown/Select (0) and Radio (2) option types are supported!', $cgi, 30, now(), NULL, NULL),

                ('General: Optional <em>Option Types</em> List', 'POSM_OPTIONAL_OPTION_TYPES_LIST', '', 'Enter types of options to be ignored when determining if a product\'s option-combination is managed, using a packed, comma-separated list.<br><br>The built-in Zen Cart option types are Dropdown/Select (0), Text (1), Radio (2), Checkbox (3), File (4) and Read-Only (5).', $cgi, 32, now(), NULL, NULL),

                ('General: Optional <em>Option Names</em> List', 'POSM_OPTIONAL_OPTION_NAMES_LIST', '', 'Enter the list of optional product options for your store, using a packed, comma-separated list of their option_id values. All option  values associated with the options that you identify here are ignored when determining if a product\'s option-combination is managed.<br><br>Use this value in conjunction with the &quot;Optional <em>Option Types</em> List&quot; to refine your store\'s option-combination configurations.', $cgi, 35, now(), NULL, NULL),

                ('Stock Status Display: Show Messages?', 'POSM_SHOW_STOCK_MESSAGES', 'Both', 'Choose where (Store Only, Admin Only, Both or Neither) the <em>POSM</em> in-/out-of-stock messages will be displayed.<br><br>If you choose <b>Store Only</b> or <b>Both</b>, this status will be displayed to your customers on the shopping_cart, checkout_confirmation and account_history_info pages. See also the <em>Dependent Attributes: Stock Status Display</em> setting, below, which operates independently of this setting.<br><br>If you choose <b>Admin Only</b> or <b>Both</b>, the in-/out-of-stock messages will be displayed within your <em>Customers-&gt;Orders</em> details display, invoices and packing slips.', $cgi, 40, now(), NULL, 'zen_cfg_select_option([\'Both\', \'Store Only\', \'Admin Only\', \'Neither\'],'),

                ('Stock Status Display:  Messages for Unmanaged Options?', 'POSM_SHOW_UNMANAGED_OPTIONS_STATUS', 'true', 'Should the store-side processing include in-/out-of-stock messages for non-<em>POSM</em>-managed products?  If set to <b>true</b>, the messages will be displayed depending on the unmanaged product\'s stock quantity &mdash; In Stock if > 0; the default out-of-stock message (PRODUCTS_OPTIONS_STOCK_NOT_IN_STOCK), otherwise.', $cgi, 44, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Stock Status Display: Include In-Stock Status?', 'POSM_SHOW_IN_STOCK_MESSAGE', 'true', 'Choose whether to include the display of the &quot;in-stock&quot; product status, <em>wherever</em> that status-display is enabled.  If set to <b>false</b>, only out-of-stock messages will be displayed.', $cgi, 46, now(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),'),

                ('Admin: Model Number Field Width', 'POSM_ADMIN_MODEL_WIDTH', '9em', 'Use this setting to control the width of the <em>Option Model/SKU</em> field displayed by <em>Catalog :: Manage Options\' Stock</em> and <em>Catalog :: Options\' Stock &mdash; View All</em> pages.  Enter a valid CSS &quot;width&quot; value, e.g. 9em (default) or 9px.<br><br><b>Note:</b> Leave the setting blank to use the database-defined field width.<br>', $cgi, 50, now(), NULL, NULL),

                ('Dependent Attributes: Enable', 'POSM_DEPENDENT_ATTRS_ENABLE', 'true', 'Identify whether or not the plugin\'s dependent-attributes processing should be enabled.', $cgi, 100, now(), NULL , 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Dependent Attributes: Insert &quot;Please Choose&quot;?', 'POSM_DEPENDENT_ATTRS_PLEASE_CHOOSE', 'true', 'Identify whether or not the plugin\'s dependent-attributes processing should insert a &quot;Please Choose&quot; selection into a product\'s drop-down options.  If <em>false</em>, the first option value for each attribute is <em>assumed</em> to be a &quot;Please choose &hellip;&quot; type value.<br><br><b>Note:</b> This setting <b><i>does not</i></b> apply when a product has a single drop-down option.', $cgi, 105, now(), NULL , 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Dependent Attributes: Show Model Number?', 'POSM_DEPENDENT_ATTRS_SHOW_MODEL', 'false', 'Identify whether or not the plugin\'s dependent-attributes processing should include the model-numbers for each attribute value on the final, selectable attribute\'s option.<br><br><strong>Note:</strong> A model-number is included <b>only if</b> that value is not an empty string.', $cgi, 109, now(), NULL , 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Dependent Attributes: Show Stock Status?', 'POSM_DEPENDENT_ATTRS_STOCK_STATUS', 'true', 'Identify whether or not the plugin\'s dependent-attributes processing should include the in-/out-of-stock status for each attribute value on the final, selectable attribute\'s option.<br><br><strong>Note:</strong> In-stock status is included <em>only</em> if <em>Stock Status Display: Include In-Stock Status?</em> is set to <b>true</b>.', $cgi, 110, now(), NULL , 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Dependent Attributes: Show In-Stock Quantity in Status?', 'POSM_DEPENDENT_ATTRS_STOCK_STATUS_QTY', 'true', 'When <em>Dependent Attributes: Stock Status Display</em> and <em>Stock Status Display: Include In-Stock Status?</em> are both <b>true</b>, should the in-stock quantity be displayed when the option-combination is <em>In Stock</em>?', $cgi, 111, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Dependent Attributes: Outer Selector', 'POSM_ATTRIBUTE_WRAPPER_SELECTOR', '', 'Identify the <em>outer</em> CSS selector (default: an empty string) that wraps <b>all</b> elements associated with a single option.<br><br><b>Notes:</b><ol><li>For Zen Cart\'s built-in <code>responsive_classic</code> and <code>template_default</code> templates (and clones thereof), this value should be set to <em>.attribBlock</em>.</li><li>For the <code>bootstrap</code> template (and clones thereof), this value should be set to an empty string.</li></ul>', $cgi, 114, now(), NULL, NULL),

                ('Dependent Attributes: Inner Selector', 'POSM_ATTRIBUTE_SELECTOR', '.wrapperAttribsOptions', 'Identify the <em>inner</em> CSS selector (default: <em>.wrapperAttribsOptions</em>) that contains, at a minimum, each option\'s name. <b>Note:</b> This value should be changed <em>only</em> if your custom template has modified the attributes\' display formatting.', $cgi, 115, now(), NULL, NULL),

                ('Dependent Attributes: Option Name Selector', 'POSM_OPTION_NAME_SELECTOR', '.optionName', 'Identify the CSS selector (default: <em>.optionName</em>) that identifies the element that contains the current option\'s name. <b>Note:</b> This value should be changed only if your custom template has modified the attributes\' display formatting.', $cgi, 116, now(), NULL, NULL),

                ('Dependent Attributes: Attributes\' Images\' Selector', 'POSM_ATTRIBUTE_IMAGE_SELECTOR', '.attribImg', 'Identify the CSS selector (default: <em>.attribImg</em>) that wraps, if configured, each attribute\'s image. <b>Note:</b> This value should be changed only if your custom template has modified the attributes\' display formatting.', $cgi, 117, now(), NULL, NULL),

                ('Dependent Attributes: Use Minified Script File?', 'POSM_USE_MINIFIED_JSCRIPT', 'true', 'Identify whether or not the plugin\'s dependent-attributes processing should load the minified version of the jQuery script, reducing the page-load time for a product\'s page.', $cgi, 120, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('View All: Allow Model Number Updates?', 'POSM_VIEW_ALL_MODEL_UPDATE', 'true', 'Should the &quot;View All&quot; processing for <em>Catalog->Manage Options\' Stock</em> enable the managed variants\' model numbers to be updated?  If set to <b>false</b>, the model numbers are displayed but cannot be updated; you\'ll need to update the model numbers on a product-by-product basis.', $cgi, 150, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('View All: Maximum Products/Page', 'POSM_MAX_PRODUCTS_VIEW_ALL', '20', 'Enter the maximum number of products to display on a single page of the <em>View All</em> tool.', $cgi, 152, now(), NULL, NULL),

                ('Shopping Cart: Display Model Numbers?', 'POSM_CART_DISPLAY_MODEL_NUMBERS', 'false', 'Should the <code>shopping_cart</code> page display a product\'s model-number?  If you choose <code>true</code>, a product\'s model number is appended to its name for the display, e.g. &quot;Product Name [model]&quot;, if the model-number is not empty.', $cgi, 200, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

                ('Enable debug?', 'POSM_ENABLE_DEBUG', 'false', 'If enabled, the <em>POSM</em> processing will write debug information to either a myDEBUG-POSM-*.log (for store-side actions) or a myDEBUG-POSM-adm-*.log (for admin-side actions) file in your store\'s \logs directory.', $cgi, 499, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],')";
        $this->executeInstallerSql($sql);

        // ----
        // Create each of the database tables for the options' stock records.
        //
        define('TABLE_PRODUCTS_OPTIONS_STOCK', DB_PREFIX . 'products_options_stock');
        define('TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES', DB_PREFIX . 'products_options_stock_attributes');
        define('TABLE_PRODUCTS_OPTIONS_STOCK_NAMES', DB_PREFIX . 'products_options_stock_names');

        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK . " (
            pos_id int(11) NOT NULL auto_increment,
            products_id int(11) NOT NULL default 0,
            pos_name_id int(11) NOT NULL default 1,
            products_quantity float NOT NULL default 0,
            pos_hash char(32) NOT NULL default '',
            pos_model varchar(32) default NULL,
            pos_date date NOT NULL default '0001-01-01',
            last_modified datetime NOT NULL default '0001-01-01 00:00:00',
            PRIMARY KEY (pos_id),
            KEY idx_posm_pid (products_id),
            KEY `posm_model` (`pos_model`)
        ) ENGINE=MyISAM";
        $this->executeInstallerSql($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " (
            pos_attribute_id int(11) NOT NULL auto_increment,
            pos_id int(11) NOT NULL default 0,
            products_id int(11) NOT NULL default 0,
            options_id int(11) NOT NULL default 0,
            options_values_id int(11) NOT NULL default 0,
            PRIMARY KEY (pos_attribute_id),
            KEY `posm_option_id` (`options_id`),
            KEY posm_options_values_id (options_values_id),
            KEY `posm_pos_id` (`pos_id`)
        ) ENGINE=MyISAM";
        $this->executeInstallerSql($sql);

        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . " (
            pos_name_id int(11) NOT NULL default 0,
            language_id int(11) NOT NULL default 1,
            pos_name varchar(64) NOT NULL default '',
            PRIMARY KEY (pos_name_id, language_id)
        ) ENGINE=MyISAM";
        $this->executeInstallerSql($sql);

        $languages = zen_get_languages();
        foreach ($languages as $current_language) {
            $this->executeInstallerSql(
                "INSERT IGNORE INTO " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
                    (pos_name_id, language_id, pos_name)
                 VALUES
                    (1, " . $current_language['id'] . ", 'Back-ordered')"
            );
        }

        // -----
        // Record the plugin's base tools in the admin menus.
        //
        zen_deregister_admin_pages([
            'configOptionsStock',
            'catalogOptionsStock',
            'catalogOptionsStockViewAll',
            'reportsOptionsStock',
            'localizationOptionsStock',
            'convertSBA2POSM',
            'toolsOptionsStockDupModels',
        ]);
        zen_register_admin_page('configOptionsStock', 'BOX_CONFIGURATION_PRODUCTS_OPTIONS_STOCK', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');
        zen_register_admin_page('catalogOptionsStock', 'BOX_CATALOG_PRODUCTS_OPTIONS_STOCK', 'FILENAME_PRODUCTS_OPTIONS_STOCK', '', 'catalog', 'Y');
        zen_register_admin_page('catalogOptionsStockViewAll', 'BOX_CATALOG_PRODUCTS_OPTIONS_STOCK_VIEW_ALL', 'FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL', '', 'catalog', 'Y');
        zen_register_admin_page('reportsOptionsStock', 'BOX_REPORTS_PRODUCTS_OPTIONS_STOCK', 'FILENAME_PRODUCTS_OPTIONS_STOCK_REPORT', '', 'reports', 'Y');
        zen_register_admin_page('localizationOptionsStock', 'BOX_LOCALIZATION_PRODUCTS_OPTIONS_STOCK', 'FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES', '', 'localization', 'Y');
        zen_register_admin_page('convertSBA2POSM', 'BOX_TOOLS_CONVERT_SBA2POSM', 'FILENAME_CONVERT_SBA2POSM', '', 'extras', 'Y');
        zen_register_admin_page('toolsOptionsStockDupModels', 'BOX_TOOLS_POSM_FIND_DUPMODELS', 'FILENAME_POSM_FIND_DUPLICATE_MODELNUMS', '', 'tools', 'Y');

        // -----
        // If a previous (non-encapsulated) version of the plugin is currently installed,
        // perform any version-specific updates needed.
        //
        if (defined('POSM_MODULE_VERSION')) {
            $this->updateFromNonEncapsulatedVersion();

            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key IN (
                    'PRODUCTS_OPTIONS_STOCK_REORDER_LEVEL',
                    'POSM_OOS_DATE_REMINDER',
                    'POSM_MODULE_VERSION',
                    'POSM_MODULE_RELEASE_DATE'
                  )"
            );
        }

        return true;
    }

    // -----
    // Not used, initially, but included for the possibility of future upgrades!
    //
    // Note: This (https://github.com/zencart/zencart/pull/6498) Zen Cart PR must
    // be present in the base code or a PHP Fatal error is generated due to the
    // function signature difference.
    //
    protected function executeUpgrade($oldVersion)
    {
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages([
            'configOptionsStock',
            'catalogOptionsStock',
            'catalogOptionsStockViewAll',
            'reportsOptionsStock',
            'localizationOptionsStock',
            'convertSBA2POSM',
            'toolsOptionsStockDupModels',
        ]);

        $cgi = $this->getConfigGroupId(
            $this->configGroupTitle,
            $this->configGroupTitle . ' Settings'
        );
        $sql = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = $cgi";
        $this->executeInstallerSql($sql);
        $sql = "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = $cgi LIMIT 1";
        $this->executeInstallerSql($sql);

        // -----
        // Uncomment these lines if you want to remove the plugin's added
        // database tables as well.
        //
//        $this->executeInstallerSql("DROP TABLE IF EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK);
//        $this->executeInstallerSql("DROP TABLE IF EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES);
//        $this->executeInstallerSql("DROP TABLE IF EXISTS " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES);
    }

    protected function executeInstallerSelectSql(string $sql)
    {
        $this->dbConn->dieOnErrors = false;
        $result = $this->dbConn->Execute($sql);
        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, true, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }
        $this->dbConn->dieOnErrors = true;
        return $result;
    }

    protected function getConfigGroupId(string $config_group_title, string $config_group_description): int
    {
        $sql =
            "SELECT configuration_group_id
               FROM " . TABLE_CONFIGURATION_GROUP . "
              WHERE configuration_group_title = '$config_group_title'
              LIMIT 1";
        $check = $this->executeInstallerSelectSql($sql);
        if (!$check->EOF) {
            return (int)$check->fields['configuration_group_id'];
        }

        $sql =
            "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                (configuration_group_title, configuration_group_description, sort_order, visible)
             VALUES
                ('$config_group_title', '$config_group_description', 1, 1)";
        $this->executeInstallerSql($sql);
        $sql = "SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '$config_group_title' LIMIT 1";
        $config_group = $this->executeInstallerSelectSql($sql);
        $cgi = (int)$config_group->fields['configuration_group_id']; 
        $sql = "UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi LIMIT 1";
        $this->executeInstallerSql($sql);
        return $cgi;
    }

    protected function nonEncapsulatedVersionPresent(): bool
    {
        $defines_to_check = [
            'TABLE_PRODUCTS_OPTIONS_STOCK',
            'TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES',
            'TABLE_PRODUCTS_OPTIONS_STOCK_NAMES',
            'FILENAME_PRODUCTS_OPTIONS_STOCK_REPORT',
            'FILENAME_PRODUCTS_OPTIONS_STOCK',
            'FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL',
            'FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES',
            'FILENAME_CONVERT_SBA2POSM',
            'FILENAME_POSM_FIND_DUPLICATE_MODELNUMS',
            'FILENAME_CATALOG_POS_EXTRA_DEFINITIONS',
        ];
        foreach ($defines_to_check as $next_define) {
            if (defined($next_define)) {
                return true;
            }
        }
        return false;
    }
    
    protected function updateFromNonEncapsulatedVersion()
    {
        switch (true) {
            // -----
            // v1.6.0: Adds 'products_id' index to database table 'products_options_stock, 'View All' tool.
            //
            case version_compare(POSM_MODULE_VERSION, '1.6.0', '<'):
                $index_check = $this->executeInstallerSelectSql(
                    "SHOW KEYS FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                    WHERE key_name='idx_posm_pid'"
                );
                if ($index_check->EOF) {
                    $this->executeInstallerSql(
                        "ALTER TABLE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                           ADD INDEX idx_posm_pid (products_id)"
                    );
                }
                                                                        //-Fall through from above to continue with updates
            // -----
            // v1.6.1: Various updates to descriptions, titles and sort-orders for configuration settings.
            //
            case version_compare(POSM_MODULE_VERSION, '1.6.1', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'Stock Status Display: Include In-Stock Status?',
                            sort_order = 46,
                            configuration_description = 'Choose whether to include the display of the &quot;in-stock&quot; product status, <em>wherever</em> that status-display is enabled.  If set to <b>false</b>, only out-of-stock messages will be displayed.'
                      WHERE configuration_key = 'POSM_SHOW_IN_STOCK_MESSAGE'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'Dependent Attributes: Stock Status Display',
                            configuration_description = 'Identify whether or not the plugin\'s dependent-attributes processing should include the in-/out-of-stock status for each attribute value on the final, selectable attribute\'s option.<br><br><strong>Note:</strong> In-stock status is included <em>only</em> if <em>Stock Status Display: Include In-Stock Status?</em> is set to <b>true</b>.'
                      WHERE configuration_key = 'POSM_DEPENDENT_ATTRS_STOCK_STATUS'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_description = 'When <em>Dependent Attributes: Stock Status Display</em> and <em>Stock Status Display: Include In-Stock Status?</em> are both <b>true</b>, should the in-stock quantity be displayed when the option-combination is <em>In Stock</em>?'
                      WHERE configuration_key = 'POSM_DEPENDENT_ATTRS_STOCK_STATUS_QTY'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET sort_order = 499,
                            configuration_description = 'If enabled, the <em>POSM</em> processing will write debug information to either a myDEBUG-POSM-*.log (for store-side actions) or a myDEBUG-POSM-adm-*.log (for admin-side actions) file in your store\'s \logs directory.'
                      WHERE configuration_key = 'POSM_ENABLE_DEBUG'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET sort_order = 20,
                            configuration_title = 'General: Enable Products\' Options\' Stock Manager?'
                      WHERE configuration_key = 'POSM_ENABLE'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET sort_order = 25,
                            configuration_title = 'General: Divider Color', configuration_description = 'Enter the background color to be used for the divider in <em>Catalog->Manage Options\' Stock</em>.'
                      WHERE configuration_key = 'POSM_DIVIDER_COLOR'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'General: Option Types to Manage?'
                      WHERE configuration_key = 'POSM_OPTIONS_TYPES_TO_MANAGE'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'General: Optional <em>Option Types</em> List'
                      WHERE configuration_key = 'POSM_OPTIONAL_OPTION_TYPES_LIST'
                      LIMIT 1"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'General: Optional <em>Option Names</em> List'
                      WHERE configuration_key = 'POSM_OPTIONAL_OPTION_NAMES_LIST'
                      LIMIT 1"
                );
                                                                        //-Fall through from above to continue with updates
             // -----
            // v2.1.0: Updates 'View All' tool to display on menu.
            //
            case version_compare(POSM_MODULE_VERSION, '2.1.0', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_ADMIN_PAGES . "
                        SET display_on_menu = 'Y'
                      WHERE page_key = 'catalogOptionsStockViewAll'
                      LIMIT 1"
                );
                                                                      //-Fall through from above to continue with updates
            // -----
            // v2.1.8: Update description for 'Please Choose' setting
            //
            case version_compare(POSM_MODULE_VERSION, '2.1.8', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_description = 'Identify whether or not the plugin\'s dependent-attributes processing should insert a &quot;Please Choose&quot; selection into a product\'s drop-down options.  If <em>false</em>, the first option value for each attribute is <em>assumed</em> to be a &quot;Please choose &hellip;&quot; type value.<br><br><b>Note:</b> This setting <b><i>does not</i></b> apply when a product has a single drop-down option.'
                      WHERE configuration_key = 'POSM_DEPENDENT_ATTRS_PLEASE_CHOOSE'
                      LIMIT 1"
                );
                                                                     //-Fall through from above to continue with updates
            // -----
            // v4.2.0: Renames 'Dependent Attributes: CSS Selector' to 'Dependent Attributes: Inner Selector'.
            //
            case version_compare(POSM_MODULE_VERSION, '4.2.0', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_title = 'Dependent Attributes: Inner Selector',
                            configuration_description = 'Identify the <em>inner</em> CSS selector (default: <em>.wrapperAttribsOptions</em>) that contains, at a minimum, each option\'s name. <b>Note:</b> This value should be changed <em>only</em> if your custom template has modified the attributes\' display formatting.'
                      WHERE configuration_key = 'POSM_ATTRIBUTE_SELECTOR'
                      LIMIT 1"
                );
                                                                   //-Fall through from above to continue with updates
            // -----
            // v4.2.1: Additional indices added to the 'products_options_stock_attributes' table, helping with
            // performance/time-out issues for sites with large numbers of option-combinations.  In addition to the
            // auto-increment index, there are now also indices on the 'pos_id', 'options_id' and 'options_values_id' fields.
            //
            // In a similar vein, add an index on products_options_stock::pos_model; the duplicate-model checks get bogged
            // down, otherwise.
            //
            case version_compare(POSM_MODULE_VERSION, '4.2.1', '<'):
                $new_indices = [
                    'options_id' => 'ADD INDEX `posm_option_id` (`options_id`)',
                    'options_values_id' => 'ADD INDEX posm_options_values_id (options_values_id)',
                    'pos_id' => 'ADD INDEX `posm_pos_id` (`pos_id`)',
                ];

                // -----
                // Gather the current indices for the table.  That information will be used to
                // 'whittle down' the list of new indices to be created.
                //
                $indices = $this->executeInstallerSelectSql(
                    "SHOW INDEX FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES
                );
                foreach ($indices as $value) {
                    unset($new_indices[$value['Column_name']]);
                }
                if (count($new_indices) != 0) {
                    $add_indices = implode(',', array_values($new_indices));
                    $this->executeInstallerSql(
                        "ALTER TABLE " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " $add_indices"
                    );
                }

                $indices = $this->executeInstallerSelectSql(
                    "SHOW INDEX FROM " . TABLE_PRODUCTS_OPTIONS_STOCK
                );
                $model_index_present = false;
                foreach ($indices as $value) {
                    if ($value['Column_name'] === 'pos_model') {
                        $model_index_present = true;
                        break;
                    }
                }
                if ($model_index_present === false) {
                    $this->executeInstallerSql(
                        "ALTER TABLE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                            ADD INDEX `posm_model` (`pos_model`)"
                    );
                }
                                                                   //-Fall through from above to continue with updates
            // -----
            // v4.3.0: Update description of POSM_ADMIN_MODEL_WIDTH to indicate that it can be left blank, in
            // which case the Model field's width will be based on the database-field's length.
            //
            case version_compare(POSM_MODULE_VERSION, '4.3.0', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_description = 'Use this setting to control the width of the <em>Option Model/SKU</em> field displayed by <em>Catalog-&gt;Manage Options\' Stock</em> and <em>Catalog-&gt;Options\' Stock &mdash; View All</em> pages.  Enter a valid CSS &quot;width&quot; value, e.g. 9em (default) or 9px.<br><br><b>Note:</b> Leave the setting blank to use the database-defined field width.<br>'
                      WHERE configuration_key = 'POSM_ADMIN_MODEL_WIDTH'
                      LIMIT 1"
                );
                                                                   //-Fall through from above to continue with updates
            // -----
            // v4.3.1:
            //
            // - Update set_function of POSM_MODULE_VERSION and POSM_MODULE_RELEASE_DATE to use 'zen_cfg_read_only('.
            // - Update description of POSM_ENABLE to indicate that it's disabling *storefront* operations only.
            //
            case version_compare(POSM_MODULE_VERSION, '4.3.1', '<'):
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET set_function = 'zen_cfg_read_only('
                      WHERE configuration_key = 'POSM_MODULE_RELEASE_DATE'
                         OR configuration_key = 'POSM_MODULE_VERSION'"
                );
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_description = 'Enable the <em>Products\' Options\' Stock</em> processing for the storefront?'
                      WHERE configuration_key = 'POSM_ENABLE'
                      LIMIT 1"
                );
                                                                   //-Fall through from above to continue with updates
            // -----
            // v4.4.0:
            //
            // - Ensure that all POSM-managed products' quantities accurately reflect the sum of their
            //   variants' quantities.
            // - Update the description of the "Dependent Attributes: Outer Selector" to indicate the
            //   required values for Zen Cart's built-in and the Bootstrap template.
            //
            case version_compare(POSM_MODULE_VERSION, '4.4.0', '<'):
                $posm_managed_products = $this->executeInstallerSelectSql(
                    "SELECT DISTINCT products_id
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK
                );
                foreach ($posm_managed_products as $posm_product) {
                    $quantity_sum = $this->executeInstallerSelectSql(
                        "SELECT SUM(products_quantity) as quantity
                           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                          WHERE products_id = " . $posm_product['products_id']
                    );
                    if ($quantity_sum->fields['quantity'] === null) {
                        continue;
                    }
                    $this->executeInstallerSql(
                        "UPDATE " . TABLE_PRODUCTS . "
                            SET products_quantity = " . $products_quantity . "
                          WHERE products_id = $pID
                          LIMIT 1"
                    );
                }
                $this->executeInstallerSql(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_description = 'Identify the <em>outer</em> CSS selector (default: an empty string) that wraps <b>all</b> elements associated with a single option.<br><br><b>Notes:</b><ol><li>For Zen Cart\'s built-in <code>responsive_classic</code> and <code>template_default</code> templates (and clones thereof), this value should be set to <em>.attribBlock</em>.</li><li>For the <code>bootstrap</code> template (and clones thereof), this value should be set to an empty string.</li></ul>'
                      WHERE configuration_key = 'POSM_ATTRIBUTE_WRAPPER_SELECTOR'
                      LIMIT 1"
                );
                                                                   //-Fall through from above to continue with updates
            // -----
            // END version-specific updates.
            //
            default:
                break;
        }
    }
}
