<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Spatie\Tags\Tag;

class TaskController extends Controller
{

    public function checkPermissions()
    {
        if (!auth()->user()->can('tasks crud')) {
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
    public function index(Status $status): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $search = request()->input('search');
        $tasks = $status->tasks();

        if($search) {
            $tasks->where('title', 'LIKE', '%' . $search . '%');
        }

        $tasks = $tasks
            ->paginate(20)
            ->through(function ($task) use ($status) {
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
                        'id' => $status->id,
                        'title' => $status->title,
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
            'message' => 'successfully retrieved.',
            'data' => [
                'tasks' => $tasks,
            ]
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Status $status): \Illuminate\Http\JsonResponse
    {

        $this->checkPermissions();

        $validated = request()->validate([
            'title' => 'required|min:2|max:24',
            'description' => 'nullable|max:1000',
            'due_date' => 'nullable|date|after:yesterday',
            'assignee_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240'
        ]);

        $validated['status_id'] = $status->id;

        $task = Task::create($validated);

        if (request()->hasFile('image')) {
            $task->addMedia(request()->file('image'))
                ->usingFileName(time() . rand(10000, 9999) . '.' . request()->file('image')->getClientOriginalExtension())
                ->toMediaCollection('image');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'new task created successfully.',
            'data' => [
                'task' => [
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
                        'name' => $task->assignee->name,
                    ],
                    'status' => [
                        'id' => $status->id,
                        'title' => $status->title,
                    ],
                    'labels' => $task->tagsWithType('label')->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name
                        ];
                    }),
                    'created_at' => $task->created_at->format('Y-m-d')
                ]
            ]
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $task = $status->tasks()->find($task_id);

        if ($task) {
            return response()->json([
                'status' => 'success',
                'message' => 'successfully retrieved task details.',
                'data' => [
                    'task' => [
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
                            'name' => $task->assignee->name,
                        ],
                        'status' => [
                            'id' => $status->id,
                            'title' => $status->title,
                        ],
                        'labels' => $task->tagsWithType('label')->map(function ($label) {
                            return [
                                'id' => $label->id,
                                'name' => $label->name
                            ];
                        }),
                        'created_at' => $task->created_at->format('Y-m-d')

                    ]
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'task was not found.',
            'data' => null,
        ], 404);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $task = $status->tasks()->find($task_id);

        if ($task) {

            $updatable = [
                'title', 'description', 'due_date', 'assignee_id', 'status_id', 'image'
            ];

            $rules = [
                'title' => 'required|min:2|max:24',
                'description' => 'nullable|max:1000',
                'due_date' => 'nullable|date|after:yesterday',
                'assignee_id' => 'required|exists:users,id',
                'status_id' => 'required|exists:statuses,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240'
            ];

            $preparedRules = [];

            foreach ($updatable as $item) {
                if (request()->has($item) && request()->filled($item)) {
                    $preparedRules[$item] = $rules[$item];
                }
            }

            $validated = request()->validate($preparedRules);

            if(!array_key_exists('status_id', $validated)) {
                $validated['status_id'] = $status->id;
            }

            $beforeUpdate = $task;

            if ($task->update($validated)) {

                TaskLog::create([
                    'user_id' => auth()->user()->id,
                    'task_id' => $task_id,
                    'details' => [
                        'message' => 'Task updated.',
                        'before_update' => $beforeUpdate,
                        'after_update' => $task,
                    ],
                ]);

                if (request()->hasFile('image')) {

                    $task->clearMediaCollection('image');

                    $task->addMedia(request()->file('image'))
                        ->usingFileName(time() . rand(10000, 9999) . '.' . request()->file('image')->getClientOriginalExtension())
                        ->toMediaCollection('image');
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'successfully updated task.',
                    'data' => [
                        'task' => [
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
                                'name' => $task->assignee->name,
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
                        ]
                    ]
                ], 200);

            }

            return response()->json([
                'status' => 'fail',
                'message' => 'something went wrong during board update.',
                'data' => [
                    'request' => \request()->all()
                ]
            ], 500);

        }

        return response()->json([
            'status' => 'fail',
            'message' => 'task was not found.',
            'data' => null,
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {
        $this->checkPermissions();

        $task = $status->tasks()->find($task_id);

        if ($task) {

            $deleted_task = $task;
            $task->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'successfully deleted task.',
                'data' => [
                    'deleted_task' => [
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
                            'name' => $task->assignee->name,
                        ],
                        'status' => [
                            'id' => $status->id,
                            'title' => $status->title,
                        ],
                        'labels' => $task->tagsWithType('label')->map(function ($label) {
                            return [
                                'id' => $label->id,
                                'name' => $label->name
                            ];
                        }),
                        'created_at' => $task->created_at->format('Y-m-d')
                    ]
                ]
            ], 200);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'task was not found.',
                'data' => null,
            ], 404);

        }
    }

    public function getLog(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {

        $task = $status->tasks()->find($task_id);

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

    public function attachLabel(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {

        $validated = request()->validate([
            'label_id' => 'required',
        ]);

        $label = Tag::getWithType('label')->where('id', $validated['label_id'])->first();

        if (!$label) {
            return response()->json([
                'status' => 'fail',
                'message' => 'label was not found.',
                'data' => null,
            ], 404);
        }

        $task = $status->tasks()->find($task_id);

        if (!$task) {
            return response()->json([
                'status' => 'fail',
                'message' => 'task was not found.',
                'data' => null,
            ], 404);
        }

        $task->attachTag($label);

        return response()->json([
            'status' => 'success',
            'message' => 'successfully attached the label to task.',
            'data' => null,
        ], 200);


    }

    public function detachLabel(Status $status, string $task_id): \Illuminate\Http\JsonResponse
    {

        $validated = request()->validate([
            'label_id' => 'required',
        ]);

        $label = Tag::getWithType('label')->where('id', $validated['label_id'])->first();

        if (!$label) {
            return response()->json([
                'status' => 'fail',
                'message' => 'label was not found.',
                'data' => null,
            ], 404);
        }

        $task = $status->tasks()->find($task_id);

        if (!$task) {
            return response()->json([
                'status' => 'fail',
                'message' => 'task was not found.',
                'data' => null,
            ], 404);
        }

        if ($task->withAnyTags([$label])->first()) {

            $task->detachTag($label);

            return response()->json([
                'status' => 'success',
                'message' => 'successfully removed the label from task.',
                'data' => null,
            ], 200);

        } else {

            return response()->json([
                'status' => 'fail',
                'message' => 'Task does not have the specified label.',
                'data' => null,
            ], 500);

        }


    }


}
