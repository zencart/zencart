<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

/**
 * Class Currencies
 * @package ZenCart\Controllers
 */
class Currencies extends AbstractLeadController
{
    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        parent::editExecute($formValidation);
        if ($this->tplVars ['leadDefinition'] ['fields'] ['code'] ['value'] == DEFAULT_CURRENCY) {
            unset($this->tplVars ['leadDefinition'] ['fields'] ['setAsDefault']);
        }
    }

    /**
     *
     */
    public function updateCurrenciesExecute()
    {
        // @todo REFACTOR  destructive action - should be post
        zen_update_currencies();
        $this->response['redirect'] = zen_href_link(FILENAME_CURRENCIES);
    }
}
