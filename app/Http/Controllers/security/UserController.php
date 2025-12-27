<?php

namespace App\Http\Controllers\security;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }


}
