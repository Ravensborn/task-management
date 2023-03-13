<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Board;

class BoardController extends Controller
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

        $search = request()->input('search', null);
        $boards = Board::query();

        if ($search) {
            $boards->where('title', 'LIKE', '%' . $search . '%');
        }

            $boards = $boards->paginate(20)->through(function ($board) {
            return [
                'id' => $board->id,
                'title' => $board->title,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'successfully retrieved.',
            'data' => [
                'boards' => $boards,
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $validated = request()->validate([
            'title' => 'required|min:2|max:24',
        ]);

        $board = Board::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'new board created successfully.',
            'data' => [
                'board' => [
                    'id' => $board->id,
                    'title' => $board->title,
                ]
            ]
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $board = Board::find($id);

        if ($board) {
            return response()->json([
                'status' => 'success',
                'message' => 'successfully retrieved board details.',
                'data' => [
                    'board' => [
                        'id' => $board->id,
                        'title' => $board->title,
                    ]
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'board was not found.',
            'data' => null,
        ], 404);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $board = Board::find($id);

        if ($board) {

            $validated = request()->validate([
                'title' => 'required|min:2|max:24',
            ]);

            if ($board->update($validated)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'successfully updated board.',
                    'data' => [
                        'board' => [
                            'id' => $board->id,
                            'title' => $board->title,
                        ],
                    ]
                ], 200);

            }

            return response()->json([
                'status' => 'error',
                'message' => 'something went wrong during board update.',
                'data' => [
                    'request' => \request()->all()
                ]
            ], 500);

        }

        return response()->json([
            'status' => 'error',
            'message' => 'board was not found.',
            'data' => null,
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $board = Board::find($id);

        if ($board) {

            $deleted_board = $board;
            $board->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'successfully deleted board.',
                'data' => [
                    'deleted_board' => [
                        'id' => $deleted_board->id,
                        'title' => $deleted_board->title,
                    ]
                ]
            ], 200);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'board was not found.',
                'data' => null,
            ], 404


            );

        }
    }



}
