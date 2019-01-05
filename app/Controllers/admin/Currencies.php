<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers\admin;

use App\Controllers\AbstractLeadController;

/**
 * Class Currencies
 * @package App\Controllers
 */
class Currencies extends AbstractLeadController
{
    /**
     *
     */
    public function editExecute($formValidation = null)
    {
        parent::editExecute($formValidation);
        if ($this->pageDefinitionBuilder->getPageDefinition()['fields']['code']['value'] == DEFAULT_CURRENCY) {
            $this->tplVarManager->forget('pageDefinition.fields.setAsDefault');
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
