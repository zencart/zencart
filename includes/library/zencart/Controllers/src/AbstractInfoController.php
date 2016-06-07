<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace ZenCart\Controllers;

use ZenCart\FormValidation\FormValidation;
use ZenCart\Lead\Builder;
use ZenCart\QueryBuilder\QueryBuilder;
use ZenCart\Request\Request as Request;
use ZenCart\Paginator\Paginator as Paginator;
use ZenCart\ListingBox\PaginatorBuilder as PaginatorBuilder;
use ZenCart\Services\LeadRoutes as LeadService;
use ZenCart\AdminUser\AdminUser as User;
use Valitron\Validator;
/**
 * Class AbstractLeadController
 * @package ZenCart\Controllers
 */
abstract class AbstractInfoController extends AbstractAdminController
{
    /**
     * @var string
     */
    public $mainTemplate = 'tplAdminInfo.php';

    /**
     * AbstractLeadController constructor.
     * @param Request $request
     * @param $db
     * @param User $user
     */
    public function __construct(Request $request, $db, User $user)
    {
        parent::__construct($request, $db, $user);
    }

    /**
     *
     */
    public function mainExecute()
    {
    }

}
