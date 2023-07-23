<?php

namespace Tests\Fixtures\App\Http\Requests;

use Illuminate\Support\Facades\Route;
use Restive\Http\Requests\Request;
use Tests\Fixtures\Models\User;

class UserRequest extends Request
{
    public function commonRules() : array
    {
        return  [
            'age' => ['required'],
            'email' => ['required'],
        ];
    }

    public function updateRules() : array
    {
        return [
            'email' => ['unique:users,email,' . $this->id]
        ];
    }
}
