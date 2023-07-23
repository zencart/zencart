<?php
namespace Tests\Fixtures\App;

use \Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    protected $table = 'zcwilt_dummy';
    protected $fillable = ['name', 'email', 'age'];
}
