<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 07 New in v2.0.0-rc1 $
 */

class TemplateSettings extends Settings
{
    protected bool $includeConstants = true;

    public function __construct(array $settings = [])
    {
        parent::__construct();

        /**
         * If no $settings were passed to the constructor,
         * look for the global $tpl_settings array and import it.
         */
        if (empty($settings) && !empty($GLOBALS['tpl_settings'])) {
            $this->setFromArray($GLOBALS['tpl_settings']);
        }
    }
}
