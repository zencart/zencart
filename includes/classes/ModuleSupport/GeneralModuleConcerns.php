<?php

namespace Zencart\ModuleSupport;

trait GeneralModuleConcerns
{
    /**
     * @param string $defineTemplate
     * @param $default
     * @return mixed
     */
    protected function getDefine(string $defineTemplate, $default = null): mixed
    {
        $define = $this->buildDefine($defineTemplate);
        if (!defined($define)) {
            return $default;
        }
        return constant($define);
    }

    /**
     * @param $defineTemplate
     * @return string
     */
    protected function buildDefine(string $defineTemplate): string
    {
        return str_replace('%%', strtoupper($this->code), $defineTemplate);
    }
}
