<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\View;

/**
 * Class TplVarManager
 * @package ZenCart\View
 */
class TplVarManager
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
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->tplVars[$key];
    }

    /**
     * @return array
     */
    public function getTplVars()
    {
        return $this->tplVars;
    }

    /**
     * @param $tplVars
     */
    public function setTplVars($tplVars)
    {
        $this->tplVars = $tplVars;
    }

    /**
     * @param $tplVars
     */
    public function addTplVars($tplVars)
    {

        $this->tplVars = array_merge($this->tplVars, $tplVars);
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
