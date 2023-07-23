<?php
namespace Tests\Fixtures\Models;

use \Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'comment', 'published'];
}
