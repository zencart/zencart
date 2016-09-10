<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RecordCompany extends Eloquent
{
    protected $table = TABLE_RECORD_COMPANY;
    protected $primaryKey = 'record_company_id';

}
