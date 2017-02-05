<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Copyright 2014 Dimitrios Savvopoulos ds@dimsav.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class TranslatedModel
 * @package App\Models
 */
class TranslatedModel extends Eloquent
{

    /**
     * @var bool
     */
    public $translatable = true;


    /**
     * @return mixed|string
     */
    public function getTranslationTable()
    {
        if (isset($this->translationTable)) {
            return $this->translationTable;
        }
        return($this->getTable() . '_translations');
    }

    /**
     * @param $parameters
     * @return mixed
     */
    public function buildLocaleQuery(Builder $eloquentBuilder, $parameters)
    {
        $translationTable = $this->getTranslationTable();
        $localeKey = 'locale';
        $locale = $this->getLocale($parameters);
        $majorLocale = $this->getLocale($locale);
        $fallback = 'en';
        $eloquentBuilder = $eloquentBuilder->select('*')
            ->leftJoin($translationTable, $translationTable.'.'.$this->getRelationKey(), '=', $this->getTable().'.'.$this->getKeyName())
            ->where($translationTable.'.'.$localeKey, $locale);


        $eloquentBuilder = $eloquentBuilder->orWhere(function ($q) use ($translationTable, $localeKey, $majorLocale, $fallback) {
            $q->where($translationTable.'.'.$localeKey, $fallback)
                ->whereNotIn($translationTable.'.'.$this->getRelationKey(), function ($q) use ($translationTable, $localeKey, $fallback, $majorLocale) {
                    $q->select($translationTable.'.'.$this->getRelationKey())
                        ->from($translationTable)
                        ->where($translationTable.'.'.$localeKey, $majorLocale);
                });
        });
        return $eloquentBuilder;
    }

    /**
     * @param $parameters
     * @return string
     */
    protected function getLocale($parameters)
    {
        $useLocale = $parameters;
        if(!isset($parameters)) {
            $useLocale = app()->getLocale();
        }
        if (!$this->validateLocale($useLocale)) {
            echo 'OOPS';
            die();
        }
        return $useLocale;
    }

    /**
     * @param $locale
     * @return mixed
     */
    protected function getMajorLocale($locale)
    {
        $locales = explode('_', $locale);
        if (count($locales) > 1) {
            return $locales[0];
        }
        return $locale;
    }

    /**
     * @param $locale
     * @return bool
     */
    protected function validateLocale($locale)
    {
        $allowedLocales = config('translatable.locales');
        if (in_array($locale, $allowedLocales)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * @return string
     */
    public function getRelationKey()
    {
        if ($this->translationForeignKey) {
            $key = $this->translationForeignKey;
        } elseif ($this->primaryKey !== 'id') {
            $key = $this->primaryKey;
        } else {
            $key = $this->getForeignKey();
        }
        return $key;
    }
}
