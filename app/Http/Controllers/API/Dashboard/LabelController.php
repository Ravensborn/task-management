<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Spatie\Tags\Tag;

class LabelController extends Controller
{

    public function checkPermissions()
    {
        if (!auth()->user()->can('label crud')) {
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

        $labels = Tag::getWithType('label')->map(function ($label) {
            return [
                'id' => $label->id,
                'name' => $label->name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'successfully retrieved.',
            'data' => [
                'labels' => $labels,
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
            'title' => 'required|min:2|max:16',
        ]);

        $label = Tag::findOrCreate($validated['title'], 'label');

        return response()->json([
            'status' => 'success',
            'message' => 'new board created successfully.',
            'data' => [
                'label' => [
                    'id' => $label->id,
                    'name' => $label->name,
                ]
            ]
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $label = Tag::withType('label')
            ->where('id', $id)
            ->first();

        if ($label) {

            $validated = request()->validate([
                'name' => 'required|min:2|max:24',
            ]);

            if ($label->update($validated)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'successfully updated label.',
                    'data' => [
                        'label' => [
                            'id' => $label->id,
                            'name' => $label->name,
                        ],
                    ]
                ], 200);

            }

            return response()->json([
                'status' => 'error',
                'message' => 'something went wrong during label update.',
                'data' => [
                    'request' => \request()->all()
                ]
            ], 500);

        }

        return response()->json([
            'status' => 'error',
            'message' => 'label was not found.',
            'data' => null,
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $label = Tag::withType('label')
            ->where('id', $id)
            ->first();

        if ($label) {

            $deleted_label = $label;
            $label->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'successfully deleted label.',
                'data' => [
                    'deleted_label' => [
                        'id' => $deleted_label->id,
                        'name' => $deleted_label->name,
                    ]
                ]
            ], 200);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'label was not found.',
                'data' => null,
            ], 404


            );

        }
    }


}
