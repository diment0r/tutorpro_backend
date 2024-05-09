<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserByToken()
    {
        $user = auth('sanctum')->user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'User with same token does not exists',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }
}
