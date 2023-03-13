<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Status;

class StatusController extends Controller
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
    public function index(string $board_id): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $search = request()->input('search', null);

        $statuses = Status::where('board_id', $board_id);

        if($search) {
            $statuses->where('title', 'LIKE', '%' . $search . '%');
        }

        $statuses = $statuses->paginate(20)->through(function ($status) {
            return [
                'id' => $status->id,
                'title' => $status->title,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'successfully retrieved.',
            'data' => [
                'statuses' => $statuses,
            ]
        ]);
    }




}
