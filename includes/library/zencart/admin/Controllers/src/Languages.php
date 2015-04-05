<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Admin\Controllers;

/**
 * Class Languages
 * @package ZenCart\Admin\Controllers
 */
class Languages extends AbstractLeadController
{
    /**
     *
     */
    public function editExecute()
    {
        parent::editExecute();
        if ($this->tplVars ['leadDefinition'] ['fields'] ['code'] ['value'] == DEFAULT_LANGUAGE) {
            unset($this->tplVars ['leadDefinition'] ['fields'] ['setAsDefault']);
        }
    }
}
