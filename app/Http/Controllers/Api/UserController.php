<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Api\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Filtramos estrictamente los usuarios que tengan rol de revisión
        $revisores = User::where('rol', 'revision')->get();

        return response()->json($revisores);
    }
}