<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{

    public function checkPermissions()
    {
        if (!auth()->user()->can('boards crud')) {
            return response()->json([
                'status' => 'fail',
                'message' => 'insufficient permissions.',
                'data' => null,
            ], 401);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'successfully retrieved.',
            'data' => [
                'roles' => $roles,
            ]
        ]);
    }




}
