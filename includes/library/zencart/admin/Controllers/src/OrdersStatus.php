<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Admin\Controllers;

/**
 * Class OrdersStatus
 * @package ZenCart\Admin\Controllers
 */
class OrdersStatus extends AbstractLeadController
{
    /**
     *
     */
    public function editExecute()
    {
        parent::editExecute();
        if ($this->tplVars ['leadDefinition'] ['fields'] ['orders_status_id'] ['value'] == DEFAULT_ORDERS_STATUS_ID) {
            unset($this->tplVars ['leadDefinition'] ['fields'] ['setAsDefault']);
        }
    }

    /**
     *
     */
    public function updateExecute()
    {
        if (!$this->hasPostsCheck()) return;
        if ($this->request->has('entry_field_setAsDefault', 'post') && $this->request->has('entry_field_orders_status_id', 'post')) {
            $this->service->updateDefaultConfigurationSetting('DEFAULT_ORDERS_STATUS_ID', $this->request->readPost('entry_field_orders_status_id'));
        }
        parent::updateExecute();
    }

    /**
     *
     */
    public function insertExecute()
    {
        if (!$this->hasPostsCheck()) return;
        $insertId = $this->service->insertExecute(true);
        if ($this->request->has('entry_field_setAsDefault', 'post')) {
            $this->service->updateDefaultConfigurationSetting('DEFAULT_ORDERS_STATUS_ID', $insertId);
        }
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'));
    }
}
