<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class MusicGenre
 * @package ZenCart\Model
 */
class MusicGenre extends Eloquent
{
    protected $table = TABLE_MUSIC_GENRE;
    protected $primaryKey = 'music_genre_id';

}
