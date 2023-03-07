<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('users crud')) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Insufficient Permissions.',
                'data' => null,
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully retrieved.',
            'data' => [
                'users' => User::paginate(20),
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(): \Illuminate\Http\JsonResponse
    {

        if (!auth()->user()->can('users crud')) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Insufficient Permissions.',
                'data' => null,
            ], 401);
        }

        $validated = request()->validate([
            'name' => 'required|min:2|max:24',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        $user = User::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'New user created successfully.',
            'data' => [
                'user' => $user
            ]
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
