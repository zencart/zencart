<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

/**
 * Class MusicGenre
 * @package ZenCart\Controllers
 */
class MediaManagerClips extends AbstractLeadController
{
    /**
     *
     */
    public function addExecute()
    {
        parent::addExecute();
        $this->tplVars ['leadDefinition'] ['fields'] ['media_id'] ['value'] = $this->request->readGet('media_id');
    }

    /**
     *
     */
    public function insertExecute()
    {
        $this->service->insertExecute();
        $this->response['redirect'] = zen_href_link($this->request->readGet('cmd'), 'media_id=' . (int)$this->request->readPost('entry_field_media_id'));
    }

}
