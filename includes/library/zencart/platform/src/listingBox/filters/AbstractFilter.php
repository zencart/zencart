<?php
/**
 * Class AbstractFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\filters;
/**
 * Class AbstractFilter
 * @package ZenCart\Platform\listingBox\filters
 */
abstract class AbstractFilter extends \base
{
    /**
     * @var
     */
    protected $request;
    /**
     * @var
     */
    protected $params;
    /**
     * @var array
     */
    protected $dbConn;
    /**
     * @var
     */
    protected $tplVars = [];

    /**
     * @param $request
     * @param $params
     */
    public function __construct($request, array $params)
    {
        $this->request = $request;
        $this->params = $params;
    }

    /**
     * @param $dbConn
     */
    public function setDBConnection($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }
}
