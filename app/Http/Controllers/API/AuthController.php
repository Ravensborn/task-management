<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;



class AuthController extends Controller
{

    public function issue_token(): \Illuminate\Http\JsonResponse
    {
        request()->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', request()->email)->first();

        if (!$user || !Hash::check(request()->password, $user->password)) {

            return response()->json([
                'status' => 'error',
                'message' => 'invalid Credentials.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'token successfully created.',
            'data' => [
                'token' => $user->createToken(request()->device_name)->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]
        ], 200);

    }
}
