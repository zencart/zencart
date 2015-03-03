<?php
/**
 * Class Paginator
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;

use Instantiator\Exception\InvalidArgumentException;

/**
 * Class Paginator
 * @package ZenCart\Platform\Paginator
 */
class Paginator extends \base
{
    /**
     * @var mixed
     */
    protected $adapter;
    
    /**
     * @var mixed
     */
    protected $scroller;

    /**
     * @param \ZenCart\Platform\Request $request
     * @param $adapterType
     * @param $scrollerType
     * @param $adapterData
     * @param $adapterParams
     * @param $scrollerParams
     */
    public function __construct(\ZenCart\Platform\Request $request, $adapterType, $scrollerType, $adapterData, $adapterParams, $scrollerParams)
    {
        $pagingVarName = isset($scrollerParams['pagingVarName']) ? $scrollerParams['pagingVarName'] : 'page';
        $pagingVarSrc = isset($scrollerParams['pagingVarSrc']) ? $scrollerParams['pagingVarSrc'] : 'get';
        $currentPage = $request->get($pagingVarName, 1, $pagingVarSrc);
        $adapterParams['currentPage'] = $currentPage;
        $scrollerParams['currentPage'] = $currentPage;
        $this->adapter = $this->buildAdapter($adapterType, $adapterData, $adapterParams);
        $this->scroller = $this->buildScroller($scrollerType, $this->adapter, $scrollerParams);
    }

    /**
     * @param $adapterType
     * @param array $adapterData
     * @param array $adapterParams
     * @return mixed
     */
    protected function buildAdapter($adapterType, array $adapterData, array $adapterParams)
    {
        $className = __NAMESPACE__ . '\\adapters\\' . ucfirst($adapterType);
        $obj = new $className($adapterData, $adapterParams);
        return $obj;
    }

    /**
     * @param $scrollerType
     * @param array $adapter
     * @param array $scrollerParams
     * @return mixed
     */
    protected function buildScroller($scrollerType, \ZenCart\Platform\Paginator\AdapterInterface $adapter, array $scrollerParams)
    {
        $className = __NAMESPACE__ . '\\scrollers\\' . ucfirst($scrollerType);
        $obj = new $className($adapter, $scrollerParams);
        return $obj;
    }

    /**
     * @return mixed
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return mixed
     */
    public function getScroller()
    {
        return $this->scroller;
    }
}
