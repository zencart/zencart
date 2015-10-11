<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

/**
 * Class TplVarManager
 * @package ZenCart\View
 */
class TplVarManager extends \base
{
    /**
     * @var array
     */
    protected $tplVars = [];

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->tplVars[$key] = $value;
    }

    /**
     * @param bool $onlyAsArray
     */
    public function globalize($onlyAsArray = false)
    {
        $tplVars = issetorArray($GLOBALS, 'tplVars', array());
        $tplVars = array_merge($tplVars, $this->tplVars);
        $GLOBALS['tplVars'] = $tplVars;
        if ($onlyAsArray) {
            return;
        }
        foreach ($this->tplVars as $tplVarName => $tplVarValue) {
            $GLOBALS[$tplVarName] = $tplVarValue;
        }
    }
}
