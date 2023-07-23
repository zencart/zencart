<?php
namespace Tests\Fixtures\Models;

use \Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    protected $table = 'dummy';
    protected $fillable = ['name', 'email', 'age'];
}
