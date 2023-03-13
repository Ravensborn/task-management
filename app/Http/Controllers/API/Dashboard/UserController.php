<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ImportUsersJob;
use App\Models\User;
use Bus;
use Str;

class UserController extends Controller
{

    public function checkPermissions()
    {
        if (!auth()->user()->can('users crud')) {
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
        $users = User::query();

        if ($search) {
            $users->where('name', 'LIKE', '%' . $search . '%');
        }

        $users = $users->paginate(20)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->name,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'successfully retrieved.',
            'data' => [
                'users' => $users,
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
            'name' => 'required|min:2|max:24',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        $user = User::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'new user created successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
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

        $user = User::find($id);

        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'successfully retrieved user.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'user was not found.',
            'data' => null,
        ], 404);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $user = User::find($id);

        if ($user) {

            $validated = request()->validate([
                'name' => 'required|min:2|max:24',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($user->update($validated)) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'successfully updated user.',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ],
                    ]
                ], 200);

            }

            return response()->json([
                'status' => 'fail',
                'message' => 'something went wrong during user update.',
                'data' => [
                    'request' => \request()->all()
                ]
            ], 500);

        }

        return response()->json([
            'status' => 'fail',
            'message' => 'user was not found.',
            'data' => null,
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $user = User::find($id);

        if ($user) {

            $deleted_user = $user;
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'successfully deleted user.',
                'data' => [
                    'deleted_user' => [
                        'id' => $deleted_user->id,
                        'name' => $deleted_user->name,
                        'email' => $deleted_user->email
                    ]
                ]
            ], 200);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'user was not found.',
                'data' => null,
            ], 404


            );

        }
    }


    public function importUsers(): \Illuminate\Http\JsonResponse
    {
        if (request()->hasFile('file')) {

            \request()->validate([
                'file' => 'required|file|mimes:csv|max:10240'
            ]);

            $file = request()->file('file');
            $filePath = storage_path('app/' . $file->store());

            $bus = Bus::batch([
                new ImportUsersJob($filePath),
            ])->name('Importing users')->dispatch();

            return response()->json([
                'batch_id' => $bus->id,
            ]);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'Please select a file.',
                'data' => null,
            ], 422);

        }
    }

    public function getImportProgress(): \Illuminate\Http\JsonResponse
    {
         $batchId = request()->get('batch_id');

         $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return response()->json([
                'status' => 'error',
                'message' => 'Batch not found.',
            ]);
        }

        $progress = $batch->progress();

        return response()->json([
            'progress' => $progress,
        ]);
    }

//    public function importUsers()
//    {
//        if (request()->hasFile('file')) {
//
//            \request()->validate([
//                'file' => 'required|file|mimes:csv|max:10240'
//            ]);
//
//            $file = request()->file('file');
//            $filename = $file->store();
//
//            ImportUsersJob::dispatch($filename)->onQueue('import');
//
//        } else {
//
//            return response()->json([
//                'status' => 'fail',
//                'message' => 'Please select a file.',
//                'data' => null,
//            ], 422);
//
//        }
//    }

//    public function importUsersProgress(): \Illuminate\Http\JsonResponse
//    {
//        $jobId = session('import_users_job_id');
//
//        if (!$jobId) {
//            return response()->json(['message' => 'No import job running.'], 404);
//        }
//
//        $job = ImportUsersJob::find($jobId);
//
//        if (!$job) {
//            return response()->json(['message' => 'Import job not found.'], 404);
//        }
//
//        return response()->json(['progress' => $job->progress()]);
//    }

}
