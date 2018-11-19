<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\Services;

use App\Controllers\AbstractAdminController as Controller;
use ZenCart\Request\Request as Request;

/**
 * Class AbstractService
 * @package ZenCart\Services
 */
Abstract class AbstractService extends \base
{
    /**
     * @var Controller
     */
    protected $listener;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var
     */
    protected $dbConn;

    /**
     * @param $listener
     * @param $request
     * @param $dbConn
     */
    public function __construct(Controller $listener, Request $request, $modelFactory)
    {
        $this->listener = $listener;
        $this->request = $request;
        $this->dbConn = $modelFactory->getConnection();
    }
}
