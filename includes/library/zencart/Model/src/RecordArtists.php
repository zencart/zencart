<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RecordArtists extends Eloquent
{
    protected $table = TABLE_RECORD_ARTISTS;
    protected $primaryKey = 'artists_id';

}
