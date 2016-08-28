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
        $this->setKeyUsinDot($this->tplVars, $key, $value);
    }

    /**
     * @param $root
     * @param $compositeKey
     * @param $value
     */
    function setKeyUsinDot(&$root, $compositeKey, $value) {
        $keys = explode('.', $compositeKey);
        while(count($keys) > 1) {
            $key = array_shift($keys);
            if(!isset($root[$key])) {
                $root[$key] = array();
            }
            $root = &$root[$key];
        }
        $key = reset($keys);
        $root[$key] = $value;
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
