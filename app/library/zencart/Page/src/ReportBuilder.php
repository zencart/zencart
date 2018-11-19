<?php
/**
 * AdminLeadBuilder Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\Page;

use \Closure;

class ReportBuilder extends AbstractBuilder
{
    public function buildPageDefinition()
    {
        $this->outputLayout['allowEdit'] = false;
        parent::buildPageDefinition();
    }
}
