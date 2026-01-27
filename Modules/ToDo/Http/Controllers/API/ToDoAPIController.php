<?php

namespace Modules\ToDo\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Modules\ToDo\Entities\ToDo;
use Modules\ToDo\Entities\TodoActivityLog;
use Modules\ToDo\Entities\TodoModuleList;
use Modules\ToDo\Entities\TodoStage;
use Modules\ToDo\Events\CompleteToDo;
use Modules\ToDo\Events\CreateToDo;
use Modules\ToDo\Events\DestroyToDo;
use Modules\ToDo\Events\UpdateToDo;
use Modules\ToDo\Events\UpdateToDoStage;
use App\Models\User;

class ToDoAPIController extends Controller
{
    /**
     * Display a listing of the ToDo resources.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
     
     
     
     
     public function index(Request $request)
{
    if (!Auth::user()->isAbleTo('todo manage')) {
        return response()->json(['error' => 'Permission denied'], 403);
    }
    
    // Get all todos for the current user and workspace
    $todos = ToDo::where([
        'created_by' => creatorId(), 
        'workspace_id' => getActiveWorkSpace()
    ])->orderBy('order')->get();
    
    // Still get stages for reference if needed
    $stages = TodoStage::where([
        'created_by' => creatorId(), 
        'workspace_id' => getActiveWorkSpace()
    ])->orderBy('order')->get();
    
    $priorities = ['All', 'Low', 'Medium', 'High'];
    
    return response()->json([
        'success' => true,
        'data' => [
            'todos' => $todos,
            'stages' => $stages,
            'priorities' => $priorities
        ]
    ]);
}
     
     
     
     
     
    // public function index(Request $request)
    // {
    //     if (!Auth::user()->isAbleTo('todo manage')) {
    //         return response()->json(['error' => 'Permission denied'], 403);
    //     }

    //     $stages = TodoStage::where([
    //         'created_by' => creatorId(), 
    //         'workspace_id' => getActiveWorkSpace()
    //     ])->orderBy('order')->get();

    //     $filter = $request->filter ?? 'All';
        
    //     foreach ($stages as $status) {
    //         $task = ToDo::query()
    //             ->orderBy('order')
    //             ->where([
    //                 'created_by' => creatorId(), 
    //                 'workspace_id' => getActiveWorkSpace()
    //             ]);

    //         if ($filter == 'All' || empty($filter)) {
    //             $status['tasks'] = $task->where('status', '=', $status->id)->get();
    //         } else {
    //             $status['tasks'] = $task->where('status', '=', $status->id)
    //                 ->where('priority', $filter)
    //                 ->get();
    //         }
    //     }

    //     $priorities = ['All', 'Low', 'Medium', 'High'];
    //     $todoStage = TodoStage::where([
    //         'workspace_id' => getActiveWorkSpace(), 
    //         'created_by' => creatorId()
    //     ])->where('name', 'Completed')->first();

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'stages' => $stages,
    //             'priorities' => $priorities,
    //             'todoStage' => $todoStage
    //         ]
    //     ]);
    // }

    /**
     * Display the ToDo board view data.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function board(Request $request)
    {
        if (!Auth::user()->isAbleTo('todo manage')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $stages = TodoStage::where([
            'created_by' => creatorId(), 
            'workspace_id' => getActiveWorkSpace()
        ])->orderBy('order')->get();

        $filter = $request->filter ?? 'All';
        
        foreach ($stages as $status) {
            $task = ToDo::query()
                ->orderBy('order')
                ->where([
                    'created_by' => creatorId(), 
                    'workspace_id' => getActiveWorkSpace()
                ]);

            if ($filter == 'All' || empty($filter)) {
                $status['tasks'] = $task->where('status', '=', $status->id)->get();
            } else {
                $status['tasks'] = $task->where('status', '=', $status->id)
                    ->where('priority', $filter)
                    ->get();
            }
        }

        $priorities = ['All', 'Low', 'Medium', 'High'];
        
        // Get todos by specific stages
        $pendingStage = TodoStage::where([
            'workspace_id' => getActiveWorkSpace(), 
            'created_by' => creatorId()
        ])->where('name', 'Pending')->first();
        
        $pendingTodo = [];
        if ($pendingStage) {
            $pendingTodo = ToDo::where([
                'created_by' => creatorId(), 
                'workspace_id' => getActiveWorkSpace()
            ])->where('status', $pendingStage->id)->get();
        }

        $upcomingStage = TodoStage::where([
            'workspace_id' => getActiveWorkSpace(), 
            'created_by' => creatorId()
        ])->where('name', 'Upcoming')->first();
        
        $upcomingTodo = [];
        if ($upcomingStage) {
            $upcomingTodo = ToDo::where([
                'created_by' => creatorId(), 
                'workspace_id' => getActiveWorkSpace()
            ])->where('status', $upcomingStage->id)->get();
        }

        $completeStage = TodoStage::where([
            'workspace_id' => getActiveWorkSpace(), 
            'created_by' => creatorId()
        ])->where('name', 'Completed')->first();
        
        $completeTodo = [];
        if ($completeStage) {
            $completeTodo = ToDo::where([
                'created_by' => creatorId(), 
                'workspace_id' => getActiveWorkSpace()
            ])->where('status', $completeStage->id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stages' => $stages,
                'priorities' => $priorities,
                'pendingTodo' => $pendingTodo,
                'upcomingTodo' => $upcomingTodo,
                'completeTodo' => $completeTodo,
                'todoStage' => $completeStage
            ]
        ]);
    }

    /**
     * Display the ToDo calendar data.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendar()
    {
        if (!Auth::user()->isAbleTo('todo manage')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $schedule = ToDo::where('created_by', creatorId())
            ->where('workspace_id', getActiveWorkSpace());

        $schedules = $schedule->get();
        $events = $schedule->whereYear('start_date', Carbon::now()->year)
            ->whereMonth('start_date', Carbon::now()->month)
            ->get();

        $arrSchedule = [];

        foreach ($schedules as $schedule) {
            $arr['id'] = $schedule['id'];
            $arr['title'] = $schedule['title'];
            $arr['start'] = $schedule['start_date'];
            $arr['end'] = $schedule['due_date'];
            
            if ($schedule['priority'] == 'High') {
                $arr['className'] = 'event-danger todo-show';
            } elseif ($schedule['priority'] == 'Medium') {
                $arr['className'] = 'event-warning todo-show';
            } else {
                $arr['className'] = 'event-success todo-show';
            }
            
            $arr['url'] = route('to-do.show', $schedule['id']);
            $arrSchedule[] = $arr;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'events' => $events,
                'schedules' => $arrSchedule
            ]
        ]);
    }

    /**
     * Store a newly created ToDo resource.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('todo create')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'assigned_to' => 'required|array',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:start_date',
                'descriptions' => 'required|array',
                'descriptions.*' => 'required|string',
                'priority' => 'required|in:Low,Medium,High',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $module = explode('-', $request->module);
        $m_name = $module[0] ?? null;
        $subm_name = $module[1] ?? null;

        // Convert descriptions into an array of objects with completed status
        $formattedDescriptions = array_map(function ($desc) {
            return ['text' => $desc, 'completed' => false];
        }, $request->descriptions);

        $stage = TodoStage::where('workspace_id', '=', getActiveWorkSpace())
            ->orderBy('order')
            ->first();
            
        if (!$stage) {
            return response()->json(['error' => 'Please add stages first'], 400);
        }

        $toDo = ToDo::create([
            'title' => $request->title,
            'assigned_to' => implode(',', $request->assigned_to),
            'description' => json_encode($formattedDescriptions),
            'status' => $stage->id,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'comments' => $request->comments,
            'module' => $m_name,
            'sub_module' => $subm_name,
            'assign_by' => Auth::user()->id,
            'created_by' => creatorId(),
            'workspace_id' => getActiveWorkSpace(),
        ]);

        TodoActivityLog::create([
            'user_id' => Auth::user()->id,
            'user_type' => get_class(Auth::user()),
            'log_type' => 'Create Task',
            'remark' => json_encode(['title' => $toDo->title]),
        ]);

        event(new CreateToDo($request, $toDo));

        return response()->json([
            'success' => true,
            'message' => 'ToDo successfully created',
            'data' => $toDo
        ], 201);
    }

    /**
     * Display the specified ToDo resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (!Auth::user()->isAbleTo('todo show')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $task = ToDo::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * Update the specified ToDo resource.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('todo edit')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'assigned_to' => 'required|array',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:start_date',
                'priority' => 'required|in:Low,Medium,High',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $module = explode('-', $request->module);
        $m_name = $module[0] ?? null;
        $subm_name = $module[1] ?? null;

        $toDo = ToDo::findOrFail($id);

        $toDo->update([
            'title' => $request->title,
            'assigned_to' => implode(',', $request->assigned_to),
            'description' => $request->description,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'module' => $m_name,
            'sub_module' => $subm_name,
            'assign_by' => Auth::user()->id,
        ]);
        
        event(new UpdateToDo($request, $toDo));

        return response()->json([
            'success' => true,
            'message' => 'ToDo successfully updated',
            'data' => $toDo
        ]);
    }

    /**
     * Remove the specified ToDo resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('todo delete')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $toDo = ToDo::findOrFail($id);
        event(new DestroyToDo($toDo));
        $toDo->delete();

        return response()->json([
            'success' => true,
            'message' => 'ToDo successfully deleted'
        ]);
    }

    /**
     * Update the task order and/or status.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function taskOrderUpdate(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'sort' => 'array',
                'id' => 'required_if:new_status,!=,old_status',
                'new_status' => 'required_if:old_status,!=,new_status',
                'old_status' => 'required_if:new_status,!=,old_status',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Update task order
        if (isset($request->sort)) {
            foreach ($request->sort as $index => $taskID) {
                $task = ToDo::find($taskID);
                $task->order = $index;
                $task->save();
            }
        }

        // Update task status
        if ($request->new_status != $request->old_status) {
            $new_status = TodoStage::find($request->new_status);
            $old_status = TodoStage::find($request->old_status);
            $user = Auth::user();
            $task = ToDo::find($request->id);
            $task->status = $request->new_status;
            $task->save();

            $id = $user->id;

            TodoActivityLog::create([
                'user_id' => $id,
                'user_type' => get_class($user),
                'log_type' => 'Move',
                'remark' => json_encode([
                    'title' => $task->title,
                    'old_status' => $old_status->name,
                    'new_status' => $new_status->name,
                ]),
            ]);
            
            event(new UpdateToDoStage($request, $task));
            
            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $task
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task order updated successfully'
        ]);
    }

    /**
     * Update the ToDo status to completed.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusUpdate($id)
    {
        if (!Auth::user()->isAbleTo('todo complete')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $todo = ToDo::find($id);
        if (!$todo) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $todoStage = TodoStage::where([
            'workspace_id' => getActiveWorkSpace(), 
            'created_by' => creatorId()
        ])->where('name', 'Completed')->first();

        if (!$todoStage) {
            return response()->json(['error' => 'Stage data mismatched'], 400);
        }

        $todo->status = $todoStage->id;
        $todo->save();

        event(new CompleteToDo($todo));

        return response()->json([
            'success' => true,
            'message' => 'You have successfully completed the to-do',
            'data' => $todo
        ]);
    }

    /**
     * Get the ToDo description.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function description($id)
    {
        $todo = ToDo::find($id);
        if (!$todo) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'description' => json_decode($todo->description, true)
            ]
        ]);
    }
   
   
   
   
    /**
     * Get the Modules.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
 public function getModules()
    {
        try {
            // Log entry to confirm the method is reached
            Log::emergency('Reached getModules endpoint with method: ' . request()->method());

            // Ensure GET method
            if (!request()->isMethod('get')) {
                Log::info('Method not allowed: ' . request()->method());
                return response()->json([
                    'status' => 0,
                    'message' => 'Method not allowed for API. Use GET.'
                ], 405);
            }

            // JWT auth (handled by middleware), verify permissions
            $user = auth('api')->user();
            if (!$user || !$user->isAbleTo('todo create')) {
                Log::info('Permission denied for user: ' . ($user ? $user->id : 'none'));
                return response()->json([
                    'status' => 0,
                    'message' => 'Permission denied'
                ], 403);
            }

            $modules = TodoModuleList::select('module', 'sub_module')->get();
            Log::info('Modules count: ' . $modules->count());
            
            if ($modules->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No modules found'
                ], 404);
            }

            $module_custumefield = [];
            if (!is_iterable($modules)) {
                Log::error('Modules is not iterable', ['modules' => $modules]);
                throw new \Exception('Invalid modules data provided');
            }

            foreach ($modules as $module) {
                if (!isset($module->module)) {
                    Log::warning('Module object missing "module" property', ['module' => $module]);
                    continue;
                }

                if (!function_exists('module_is_active')) {
                    Log::error('module_is_active function does not exist');
                    throw new \Exception('Required function module_is_active is not defined');
                }

                $isActive = module_is_active($module->module);
                if ($isActive === false && $module->module !== 'Base') {
                    Log::debug('Module is inactive and not Base', ['module' => $module->module]);
                    continue;
                }

                try {
                    $sub_module_custumefield = TodoModuleList::select('module', 'sub_module')
                        ->where('module', $module->module)
                        ->get();
                } catch (\Exception $e) {
                    Log::error('Database query failed for sub-modules', [
                        'module' => $module->module,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }

                if ($sub_module_custumefield->isEmpty()) {
                    Log::info('No sub-modules found for module', ['module' => $module->module]);
                }

                $temp = [];
                foreach ($sub_module_custumefield as $sb) {
                    if (!isset($sb->sub_module)) {
                        Log::warning('Sub-module object missing "sub_module" property', ['sub_module' => $sb]);
                        continue;
                    }

                    $key = strtolower($module->module) . '-' . strtolower($sb->sub_module);
                    $temp[$key] = $sb->sub_module;
                }

                if (!function_exists('Module_Alias_Name')) {
                    Log::error('Module_Alias_Name function does not exist');
                    throw new \Exception('Required function Module_Alias_Name is not defined');
                }

                try {
                    $alias = Module_Alias_Name($module->module);
                    if (empty($alias)) {
                        Log::warning('Module_Alias_Name returned empty for module', ['module' => $module->module]);
                        $alias = $module->module;
                    }
                    $module_custumefield[$alias] = $temp;
                } catch (\Exception $e) {
                    Log::error('Module_Alias_Name failed', [
                        'module' => $module->module,
                        'error' => $e->getMessage()
                    ]);
                    $module_custumefield[$module->module] = $temp;
                }
            }

            return response()->json([
                'status' => 1,
                'message' => 'Modules retrieved successfully',
                'data' => $module_custumefield
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getModules: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update the description item status.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDescriptionStatus(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'index' => 'required|integer',
                'completed' => 'required|boolean',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $todo = ToDo::find($id);
        if (!$todo) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $descriptions = json_decode($todo->description, true);

        if (!isset($descriptions[$request->index])) {
            return response()->json(['error' => 'Invalid index'], 400);
        }

        $descriptions[$request->index]['completed'] = $request->completed;
        $todo->description = json_encode($descriptions);
        $todo->save();

        return response()->json([
            'success' => true,
            'message' => 'Description status updated successfully',
            'data' => [
                'updated_descriptions' => $descriptions
            ]
        ]);
    }
}