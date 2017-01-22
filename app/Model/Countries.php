<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Model;

/**
 * Class Countries
 * @package ZenCart\Model
 */
class Countries extends TranslatedModel
{
    //protected $table = TABLE_ADMIN;
    protected $primaryKey = 'countries_id';
    protected $translationTable = TABLE_COUNTRIES_NAME;
    public $translatedAttributes = ['countries_name'];


}
