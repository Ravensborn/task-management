<?php

namespace App\Http\Controllers\API\Dashboard;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use App\Models\User;

class ReportController extends Controller
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


    public function completedLargestNumberOfTasks(): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $users = User::withCount(['tasks', 'tasks' => function (Builder $query) {
            //5 is equal to done status.
            $query->where('status_id',  5);
        }])
            ->orderBy('tasks_count', 'desc')
            ->paginate(20)
            ->through(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tasks_count' => $user->tasks_count,
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
    public function UsersWithTasksThatExceedDueDate(): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $users = User::withCount(['tasks', 'tasks' => function (Builder $query) {
            //5 is equal to done status.
            $query->whereDate('due_date',  '<', today());
        }])
            ->orderBy('tasks_count', 'desc')
            ->paginate(20)
            ->through(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tasks_count' => $user->tasks_count,
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



}
