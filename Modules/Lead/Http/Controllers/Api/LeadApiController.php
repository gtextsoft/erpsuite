<?php

namespace Modules\Lead\Http\Controllers\Api;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Lead\Entities\Pipeline;
use Modules\Lead\Entities\LeadUtility;
use Modules\Lead\Entities\LeadStage;
use Modules\Lead\Entities\DealStage;
use Modules\Lead\Entities\Lead;
use Illuminate\Support\Facades\Auth;
use Modules\Lead\Entities\UserLead;
use Modules\Lead\Entities\LeadActivityLog;
use Modules\Lead\Entities\LeadCall;
use Modules\Lead\Entities\LeadDiscussion;
use Modules\Lead\Entities\LeadEmail;
use Modules\Lead\Entities\LeadFile;
use Modules\Lead\Entities\LeadTask;
use App\Models\WorkSpace;
use Illuminate\Support\Str;
use Modules\Lead\Events\CreateLead;
use App\Models\User;
use Modules\CustomField\Entities\CustomField;
// use App\EmailTemplate; // Adjust namespace if EmailTemplate is elsewhere

class LeadApiController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function leadboard(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required',
                'pipeline_id'  => 'required|exists:pipelines,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;

            $pipeline = Pipeline::where('created_by', '=', creatorId())
                                    ->where('workspace_id', '=', $currentWorkspace)
                                    ->where('id', '=', $pipelineId)
                                    ->first();

            $leadStages = $pipeline->leadStages->map(function($stage){
                                                        return (object) [
                                                            'id' => $stage->id,
                                                            'name' => $stage->name,
                                                            'order' => $stage->order,
                                                        ];
                                                    });;

            $data = [];
            foreach ($leadStages as $key => $stage) 
            {
                $lead = Lead::where('created_by', '=', creatorId())
                                ->where('workspace_id', $currentWorkspace)
                                ->where('pipeline_id', $pipelineId)
                                ->where('stage_id', $stage->id)
                                ->get();

                $stage->leads =  $lead->map(function($lead)use($key ,$leadStages){
                   
                    return [
                        'id'                => $lead->id, 
                        'name'              => $lead->name, 
                        'order'             => $lead->order, 
                        'previous_stage'    => isset($leadStages[$key-1]) ? $leadStages[$key-1]->id : 0, 
                        'current_stage'     => $leadStages[$key]->id, 
                        'next_stage'        => isset($leadStages[$key+1]) ? $leadStages[$key+1]->id : 0,
                        'labels'            => $lead->labels()?$lead->labels()->map(function($label){
                                                    return [
                                                        'id'    => $label->id,
                                                        'name'  => $label->name,
                                                        'color' => $label->color,
                                                    ]   ;         
                                                }) : [], 
                        'users'             => $lead->users->map(function($user){
                                                    return [
                                                        'id'        => $user->id,
                                                        'name'      => $user->name,
                                                        'avatar'    => check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png'),
                                                    ];
                                                }), 
                    ];
                });
            }                        

            return response()->json(['status' => 1, 'data' => $leadStages] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        } 
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    // public function create()
    // {
    //     return view('lead::create');
    // }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    // public function store(Request $request)
    // {
    //     $validator = \Validator::make(
    //         $request->all(), [
    //             'workspace_id'  => 'required|exists:work_spaces,id',
    //             'pipeline_id'   => 'required|exists:pipelines,id',
    //             'phone'         => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
    //             'subject'       => 'required',
    //             'name'          => 'required',
    //             'email'         => 'required',
    //             'follow_up_date'=> 'required|date_format:Y-m-d',
    //             'lead_id'       => 'exists:leads,id',
    //         ]
    //     );

    //     if($validator->fails())
    //     {
    //         $messages = $validator->getMessageBag();

    //         return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
    //     }
    //     try{

    //         $objUser          = Auth::user();
    //         $pipelineId       = $request->pipeline_id;
    //         $currentWorkspace = $request->workspace_id;

    //         // Default Field Value
    //         $pipeline = Pipeline::where('created_by', '=', creatorId())
    //                                 ->where('workspace_id', $currentWorkspace)
    //                                 ->where('id', $pipelineId)
    //                                 ->first();

    //         if (!empty($pipeline)) {
    //             $stage = LeadStage::where('pipeline_id', '=', $pipelineId)
    //                                     ->where('workspace_id', $currentWorkspace)
    //                                     ->first();
    //         } else {
    //             return response()->json(['status' => 0, 'message' => 'Please Create Pipeline.'] , 403);
    //         }

    //         if (empty($stage)) {
    //             return response()->json(['status' => 0, 'message' => 'Please Create Stage for This Pipeline'] , 403);
    //         } else {

    //             if(empty($request->lead_id))
    //             {
    //                 $lead                 = new Lead();
    //                 $lead->name           = $request->name;
    //                 $lead->email          = $request->email;
    //                 $lead->subject        = $request->subject;
    //                 $lead->user_id        = $request->user_id;
    //                 $lead->pipeline_id    = $pipelineId;
    //                 $lead->stage_id       = $stage->id;
    //                 $lead->phone          = $request->phone;
    //                 $lead->created_by     = creatorId();
    //                 $lead->workspace_id   = $currentWorkspace;
    //                 $lead->date           = date('Y-m-d');
    //                 $lead->follow_up_date = $request->follow_up_date;
    //                 $lead->save();
    
    //                 if (Auth::user()->hasRole('company')) {
    //                     $usrLeads = [
    //                         $objUser->id,
    //                         $request->user_id,
    //                     ];
    //                 } else {
    //                     $usrLeads = [
    //                         $creatorId,
    //                         $request->user_id,
    //                     ];
    //                 }
    
    //                 foreach ($usrLeads as $usrLead) {
    //                     UserLead::create(
    //                         [
    //                             'user_id' => $usrLead,
    //                             'lead_id' => $lead->id,
    //                         ]
    //                     );
    //                 }

    //                 return response()->json(['status' => 1 , 'message' => 'Lead created Successfully.'] , 200);
    //             }else{

    //                 $lead                 = Lead::where('created_by', '=', creatorId())
    //                                                 ->where('workspace_id', $currentWorkspace)
    //                                                 ->where('id',$request->lead_id )
    //                                                 ->first();

    //                 if($lead !== null){

    //                     $lead->name           = $request->name;
    //                     $lead->email          = $request->email;
    //                     $lead->subject        = $request->subject;
    //                     $lead->user_id        = $request->user_id;
    //                     $lead->pipeline_id    = $pipelineId;
    //                     $lead->stage_id       = $stage->id;
    //                     $lead->phone          = $request->phone;
    //                     $lead->follow_up_date = $request->follow_up_date;
    //                     $lead->save();

    //                 }else{

    //                     return response()->json(['status'=> 0 ,'message' => 'Lead not found!!!']);
    //                 }

    //                 return response()->json(['status' => 1 , 'message' => 'Lead updated Successfully.'] , 200);
    //             }
    //         }

    //         return response()->json(['status' => 1, 'data' => $pipeline] , 200);

    //     } catch (\Exception $e) {
    //         return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
    //     } 
    // }
    
    
    public function store(Request $request)
{
    $usr = Auth::user();

    if (!$usr->isAbleTo('lead create')) {
        return response()->json(['status' => 0, 'message' => __('Permission Denied.')], 401);
    }

    $creatorId = creatorId();
    $getActiveWorkSpace = getActiveWorkSpace();

    // Expect an array of leads or a single lead
    $leadsData = $request->input('leads', [$request->all()]);

    $results = [];
    $errors = [];

    foreach ($leadsData as $index => $leadData) {
        // Validate each lead
        $validator = \Validator::make(
            $leadData,
            [
                'subject' => 'required',
                'name' => 'nullable',
                'email' => 'nullable|email',
                'short_description' => 'nullable',
                'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                'user_id' => 'nullable|exists:users,id',
                'follow_up_date' => 'nullable|date_format:Y-m-d',
                'customField' => 'nullable|array',
            ]
        );

        if ($validator->fails()) {
            $errors[] = ['lead_index' => $index, 'message' => $validator->getMessageBag()->first()];
            continue;
        }

        try {
            // Pipeline and stage logic (same as above)
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)
                    ->where('workspace_id', $getActiveWorkSpace)
                    ->where('id', '=', $usr->default_pipeline)
                    ->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)
                    ->where('workspace_id', $getActiveWorkSpace)
                    ->first();
            }

            if (!$pipeline) {
                $errors[] = ['lead_index' => $index, 'message' => __('Please Create Pipeline.')];
                continue;
            }

            $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)
                ->where('workspace_id', $getActiveWorkSpace)
                ->first();

            if (!$stage) {
                $errors[] = ['lead_index' => $index, 'message' => __('Please Create Stage for This Pipeline.')];
                continue;
            }

            // Create lead
            $workspaceName = Workspace::find($getActiveWorkSpace)->name ?? 'WS';
            $sanitizedWorkspace = Str::upper(Str::slug($workspaceName, '_'));
            $uniqueId = time() . '_' . $index;

            $lead = new Lead();
            $lead->name = $leadData['name'] ?? null;
            $lead->email = $leadData['email'] ?? null;
            $lead->subject = $leadData['subject'];
            $lead->user_id = $leadData['user_id'] ?? null;
            $lead->pipeline_id = $pipeline->id;
            $lead->stage_id = $stage->id;
            $lead->phone = $leadData['phone'];
            $lead->created_by = $creatorId;
            $lead->workspace_id = $getActiveWorkSpace;
            $lead->date = date('Y-m-d');
            $lead->follow_up_date = $leadData['follow_up_date'] ?? null;
            $lead->lead_id = "{$sanitizedWorkspace}_{$uniqueId}";
            $lead->short_description = $leadData['short_description'] ?? null;
            $lead->save();

            // Save custom fields
            if (module_is_active('CustomField') && !empty($leadData['customField'])) {
                CustomField::saveData($lead, $leadData['customField']);
            }

            // Assign users
            if ($usr->hasRole('company')) {
                $usrLeads = [$usr->id, $leadData['user_id']];
            } else {
                $usrLeads = [$creatorId, $leadData['user_id']];
            }

            foreach (array_filter($usrLeads) as $usrLead) {
                UserLead::create([
                    'user_id' => $usrLead,
                    'lead_id' => $lead->id,
                ]);
            }

            // Email notification
            if (!empty($company_settings['Lead Assigned']) && $company_settings['Lead Assigned'] == true && $leadData['user_id']) {
                $lArr = [
                    'lead_name' => $lead->name,
                    'lead_email' => $lead->email,
                    'lead_pipeline' => $pipeline->name,
                    'lead_stage' => $stage->name,
                ];
                $usrEmail = \App\Models\User::find($leadData['user_id']);
                if ($usrEmail) {
                    EmailTemplate::sendEmailTemplate('Lead Assigned', [$usrEmail->id => $usrEmail->email], $lArr);
                }
            }

            // Fire event
            event(new CreateLead($request, $lead));

            $results[] = [
                'lead_index' => $index,
                'status' => 1,
                'message' => __('Lead successfully created!'),
                'data' => [
                    'lead_id' => $lead->id,
                    'custom_lead_id' => $lead->lead_id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'subject' => $lead->subject,
                    'follow_up_date' => $lead->follow_up_date,
                    'pipeline_id' => $lead->pipeline_id,
                    'stage_id' => $lead->stage_id,
                ]
            ];
        } catch (\Exception $e) {
            $errors[] = ['lead_index' => $index, 'message' => __('Something went wrong!!!'), 'error' => $e->getMessage()];
        }
    }

    if (!empty($errors)) {
        return response()->json(['status' => 0, 'results' => $results, 'errors' => $errors], 403);
    }

    return response()->json(['status' => 1, 'results' => $results], 200);
}

    /**
     * Optional: API to fetch data needed for creating a lead (e.g., users, custom fields)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $usr = Auth::user();

        if (!$usr->isAbleTo('lead create')) {
            return response()->json(['status' => 0, 'message' => __('Permission Denied.')], 401);
        }

        $creatorId = creatorId();
        $getActiveWorkSpace = getActiveWorkSpace();

        // Fetch users
        if ($usr->type == "company") {
            $users = \App\Models\User::where('created_by', '=', $creatorId)
                ->where('type', '!=', 'client')
                ->where('workspace_id', $getActiveWorkSpace)
                ->get()
                ->pluck('name', 'id');
        } else {
            $users = \App\Models\User::where('id', '=', $usr->id)
                ->where('type', '!=', 'client')
                ->where('workspace_id', $getActiveWorkSpace)
                ->get()
                ->pluck('name', 'id');
        }

        $users = $users->prepend(__('Select User'), '');

        // Fetch custom fields
        $customFields = module_is_active('CustomField')
            ? CustomField::where('workspace_id', $getActiveWorkSpace)
                ->where('module', '=', 'lead')
                ->where('sub_module', 'lead')
                ->get()
            : [];

        return response()->json([
            'status' => 1,
            'data' => [
                'users' => $users,
                'customFields' => $customFields,
            ]
        ], 200);
    }
    

    public function leadDetails(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        $objUser          = Auth::user();
        $pipelineId       = $request->pipeline_id;
        $currentWorkspace = $request->workspace_id;
        $leadId           = $request->lead_id;
        
        
        try{
            
            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();
            
            $data = [
                'id'                => $lead->id,
                'name'              => $lead->name,
                'email'             => $lead->email,
                'subject'           => $lead->subject,
                'pipeline_id'       => $lead->pipeline_id,
                'stage_id'          => $lead->stage_id,
                'order'             => $lead->order,
                'phone'             => $lead->phone,
                'follow_up_date'    => $lead->follow_up_date,
                'tasks_list'        => $lead->tasks->map(function($task){
                                            return [
                                                'id'        => $task->id,
                                                'name'      => $task->name,
                                                'date'      => $task->date,
                                                'time'      => $task->time,
                                                'priority'  => LeadTask::$priorities[$task->priority],
                                                'status'    => LeadTask::$status[$task->status],
                                            ];
                                        }),

                'lead_activity'     => $lead->activities->map(function($activity){
                                                return [
                                                    'id'        => $activity->id,
                                                    // 'log_type'  => $activity->log_type,
                                                    'remark'    => strip_tags($activity->getLeadRemark()),
                                                    'time'      => $activity->created_at->diffForHumans(),
                                            ];
                                        }),
            ];

            return response()->json(['status' => 1, 'data' => $data] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        } 
    }

    public function leadStageUpdate(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
                'new_status'    => 'required|exists:lead_stages,id',
                // 'old_status'    => 'required',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{    

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;
            $leadId           = $request->lead_id;

            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();

            if ($request->new_status != $lead->stage_id) {

                $new_status   = LeadStage::where('workspace_id',$currentWorkspace)->where('created_by',creatorId())->where('id',$request->new_status)->first();
                // $old_status   = LeadStage::where('workspace_id',$currentWorkspace)->where('created_by',creatorId())->where('id',$lead->stage_id)->first();
                $lead->stage_id = $request->new_status;
                $lead->save();
            }
            return response()->json(['status' => 1 ,'message' => 'Task stage update successfully.']);

        } catch (\Exception $e) {
            return response()->json(['status' => 0 ,'message' => 'something went wrong!!!']);
        } 
    }


    public function destroy(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                'workspace_id'  => 'required|exists:work_spaces,id',
                'pipeline_id'   => 'required|exists:pipelines,id',
                'lead_id'       => 'required|exists:leads,id',
            ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return response()->json(['status' => 0, 'message' => $messages->first()] , 403);
        }

        try{

            $objUser          = Auth::user();
            $pipelineId       = $request->pipeline_id;
            $currentWorkspace = $request->workspace_id;
            $leadId           = $request->lead_id;

            $lead = Lead::where('created_by', '=', creatorId())->where('workspace_id', $currentWorkspace)->where('pipeline_id', $pipelineId)->where('id', $leadId)->first();

            LeadDiscussion::where('lead_id', '=', $lead->id)->delete();
            UserLead::where('lead_id', '=', $lead->id)->delete();
            LeadActivityLog::where('lead_id', '=', $lead->id)->delete();
            $leadfiles = LeadFile::where('lead_id', '=', $lead->id)->get();

            foreach ($leadfiles as $leadfile) {

                delete_file($leadfile->file_path);
                $leadfile->delete();
            }

            $lead->delete();

            return response()->json(['status' => 1 , 'message' => 'Lead Delete Successfully.'] , 200);

        } catch (\Exception $e) {
            return response()->json(['status'=> 0 ,'message' => 'something went wrong!!!']);
        } 
    }
}
