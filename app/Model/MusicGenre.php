<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class MusicGenre extends Eloquent
{
    protected $table = TABLE_MUSIC_GENRE;
    protected $primaryKey = 'music_genre_id';

}
