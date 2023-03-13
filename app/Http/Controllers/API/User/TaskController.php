<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskLog;

class TaskController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $search = request()->input('search', null);
        $user = auth()->user();
        $tasks = $user->tasks();

        if ($search) {
            $tasks->where('title', 'LIKE','%' . $search . '%');
        }

        $tasks = $tasks->paginate(20)
            ->through(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'image' => $task->getFirstMediaUrl('image') ? $task->getFirstMediaUrl('image') : null,
                    'due_date' => [
                        'date' => $task->due_date->format('Y-m-d'),
                        'time' => $task->due_date->format('H:i:s')
                    ],
                    'assignee' => [
                        'id' => $task->assignee->id,
                        'name' => $task->assignee->name
                    ],
                    'status' => [
                        'id' => $task->status->id,
                        'title' => $task->status->title,
                    ],
                    'labels' => $task->tagsWithType('label')->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name
                        ];
                    }),
                    'created_at' => $task->created_at->format('Y-m-d')
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'retrieved successfully.',
            'data' => $tasks
        ], 200);

    }

    public function getTaskLog(string $task_id): \Illuminate\Http\JsonResponse
    {

        $user = auth()->user();
        $task = $user->tasks()->find($task_id);

        if ($task) {

            $logs = TaskLog::where('task_id', $task_id)->paginate(20);

            return response()->json([
                'status' => 'success',
                'message' => 'task logs successfully retrieved.',
                'data' => $logs,
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'task was not found.',
            'data' => null,
        ], 404);

    }


    public function checkTaskMovingRules($user, $from, $to): bool
    {

        if ($user->hasRole('product owner')) {
            return true;
        }

        if ($user->hasRole('developer')) {

            if (($from == 1 && $to == 2) || ($from == 2 && $to == 1)) {
                return true;
            }

            if (($from == 2 && $to == 4) || ($from == 4 && $to == 2)) {
                return true;
            }

        }

        if ($user->hasRole('tester')) {

            if (($from == 3 && $to == 4) || ($from == 4 && $to == 3)) {
                return true;
            }

            if (($from == 4 && $to == 5) || ($from == 5 && $to == 4)) {
                return true;
            }

        }

        return false;
    }

    public function updateTaskStatus(string $task): \Illuminate\Http\JsonResponse
    {

        $user = auth()->user();

        //This part insures that users can only move their own tasks.

        $task = Task::where('assignee_id', $user->id)->find($task);

        if ($task) {

            $validated = \request()->validate([
                'status_id' => 'required|exists:statuses,id'
            ]);

            //Ensure that we can move tasks only from to-do to in-progress or from in-progress to testing
            if (!$this->checkTaskMovingRules($user, $task->status_id, $validated['status_id'])) {

                return response()->json([
                    'status' => 'fail',
                    'message' => 'You are not allowed to do this action.',
                    'data' => null
                ], 401);

            }


            if ($task->update(['status_id' => $validated['status_id']])) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'task successfully moved.',
                    'data' => null
                ], 200);

            }
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'task was not found.',
            'data' => null
        ], 404);


    }

    public function checkIfTaskCanBeAssignedToUser($user, $task): bool
    {

        //in-progress, dev-review
        if ($user->hasRole('developer') && in_array($task->status->id, [2, 3])) {
            return true;
        }

        //testing
        if (($user->hasRole('tester') || $user->hasRole('product owner')) && $task->status->id == 4) {
            return true;
        }

        //done, close
        if (($user->hasRole('tester') || $user->hasRole('product owner')) && in_array($task->status->id, [5, 6])) {
            return true;
        }

        return false;
    }

    protected function checkIfTaskCanBeManipulatedByUser($user, $task)
    {
        switch ($task->status_id) {
            case 1: //to-do
                return true;
            case 2: //in-progress
                return $user->hasRole('product owner') || $user->hasRole('developer');
            case 3: //dev-review
                return $user->hasRole('product owner') || $user->hasRole('developer');
            case 4: //testing
                return $user->hasRole('product owner') || $user->hasRole('tester');
            case 5: //done
                return $user->hasRole('product owner') || $user->hasRole('tester');
            case 6: //close
                return $user->hasRole('product owner');
        }
    }


    public function updateTaskAssignee(string $task): \Illuminate\Http\JsonResponse
    {

        $user = auth()->user();

        $task = Task::where('assignee_id', $user->id)->find($task);

        if ($task) {

            $validated = \request()->validate([
                'assignee_id' => 'required|exists:users,id'
            ]);

            if (!$this->checkIfTaskCanBeAssignedToUser($user, $task)) {

                return response()->json([
                    'status' => 'fail',
                    'message' => 'This task cannot be assigned to this user.',
                    'data' => null
                ], 422);

            }

            if (!$this->checkIfTaskCanBeManipulatedByUser($user, $task)) {

                return response()->json([
                    'status' => 'fail',
                    'message' => 'This task cannot be manipulated by this user.',
                    'data' => null
                ], 422);

            }

            if ($task->update(['assignee_id' => $validated['assignee_id']])) {

                return response()->json([
                    'status' => 'success',
                    'message' => 'task successfully assigned to user.',
                    'data' => null
                ], 200);

            }
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'task was not found.',
            'data' => null
        ], 404);


    }

    public function getExpiredTasks() {

        $search = request()->input('search', null);
        $user = auth()->user();
        $tasks = $user->tasks()->whereDate('due_date', '<', today());

        if ($search) {
            $tasks->where('title', 'LIKE','%' . $search . '%');
        }

        $tasks = $tasks->paginate(20)
            ->through(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'image' => $task->getFirstMediaUrl('image') ? $task->getFirstMediaUrl('image') : null,
                    'due_date' => [
                        'date' => $task->due_date->format('Y-m-d'),
                        'time' => $task->due_date->format('H:i:s')
                    ],
                    'assignee' => [
                        'id' => $task->assignee->id,
                        'name' => $task->assignee->name
                    ],
                    'status' => [
                        'id' => $task->status->id,
                        'title' => $task->status->title,
                    ],
                    'labels' => $task->tagsWithType('label')->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name
                        ];
                    }),
                    'created_at' => $task->created_at->format('Y-m-d')
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'retrieved successfully.',
            'data' => $tasks
        ], 200);

    }

}
