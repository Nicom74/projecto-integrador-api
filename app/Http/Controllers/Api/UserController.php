<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\User;

class UserController extends Controller
{
    public function index()
    {
        return User::query()
            ->where('rol', 'revision')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'rol']);
    }
}
