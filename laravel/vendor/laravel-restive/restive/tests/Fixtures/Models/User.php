<?php
namespace Tests\Fixtures\Models;

use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'age'];

    public function posts()
    {
        return $this->hasMany('Tests\Fixtures\Models\Post', 'user_id');
    }

    public function scopeTeenager($query)
    {
        return $query->whereBetween('age', [13,19]);
    }
}
