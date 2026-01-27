<?php

namespace Modules\ZoomMeeting\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Modules\ZoomMeeting\DataTables\ZoomMeetingDataTable;
use Modules\ZoomMeeting\Traits\ZoomMeetingTrait as TraitsZoomMeetingTrait;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\ZoomMeeting\Entities\GeneralMetting;
use Modules\ZoomMeeting\Entities\ZoomMeeting;
use Modules\ZoomMeeting\Events\CreateZoommeeting;
use Modules\ZoomMeeting\Events\DestroyZoommeeting;
use Carbon\Carbon;

class ZoomMeetingController extends Controller
{
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;
    const MEETING_URL = "https://api.zoom.us/v2/";

    use TraitsZoomMeetingTrait;

    public function index(ZoomMeetingDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('zoommeeting manage')) {
            return $dataTable->render('zoom-meeting::index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('zoommeeting create')) {
            if (Auth::user()->type == 'company' || \Auth::user()->type == 'super admin') {
                $users = User::where('id', '!=', \Auth::user()->id)
                    ->where('created_by', '=', \Auth::user()->id)
                    ->pluck('name', 'id');
            } else {
                $users = User::where('created_by', '=', \Auth::user()->created_by)
                    ->pluck('name', 'id');
            }

            return view('zoom-meeting::create', compact('users'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('zoommeeting create')) {

            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'duration' => 'required',
                    'start_date' => 'required',
                    'users' => 'required',
                ]
            );

            if ($validator->fails()) {
                return redirect()->route('zoom-meeting.index')->with('error', $validator->getMessageBag()->first());
            }

            $data['topic'] = $request->title;
            $data['start_time'] = date('Y-m-d H:i:s', strtotime($request->start_date));
            $data['duration'] = (int)$request->duration;
            $data['password'] = $request->password ?? null;
            $data['host_video'] = 0;
            $data['participant_video'] = 0;

            try {
                $meeting_create = $this->createmitting($data);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Invalid access token'));
            }

            if (isset($meeting_create['success']) && $meeting_create['success'] === true) {
                DB::beginTransaction();

                try {
                    $zoom = new ZoomMeeting();
                    $zoom->title = $request->title;
                    $zoom->meeting_id = $meeting_create['data']['id'] ?? 0;
                    $zoom->start_date = $data['start_time'];
                    $zoom->duration = $request->duration;
                    $zoom->start_url = $meeting_create['data']['start_url'] ?? '';
                    $zoom->join_url = $meeting_create['data']['join_url'] ?? '';
                    $zoom->status = $meeting_create['data']['status'] ?? '';
                    $zoom->password = $request->password;
                    $zoom->user_id = \Auth::user()->id;
                    $zoom->created_by = creatorId();
                    $zoom->workspace_id = getActiveWorkSpace(); // optional: retain for multi-tenant logs

                    if ($zoom->save()) {
                        foreach ($request->users as $user) {
                            GeneralMetting::create([
                                'm_id' => $zoom->id,
                                'user_id' => $user,
                            ]);
                        }
                    }

                    DB::commit();
                    event(new CreateZoommeeting($request, $zoom));

                    return redirect()->back()->with('success', __('The meeting has been created successfully.'));
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', __('Meeting not created.'));
            }
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function show($id)
    {
        if (Auth::user()->isAbleTo('zoommeeting show')) {
            $zoom_meeting = ZoomMeeting::find($id);
            return view('zoom-meeting::show', compact('zoom_meeting'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function edit($id)
    {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('zoommeeting delete')) {
            $meeting = ZoomMeeting::find($id);
            if ($meeting) {
                GeneralMetting::where('m_id', $meeting->id)->delete();
                event(new DestroyZoommeeting($meeting));
                $meeting->delete();

                return redirect()->back()->with('success', __('The zoom meeting has been deleted.'));
            } else {
                return redirect()->back()->with('success', __('Zoom meeting not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function setting(Request $request)
    {
        if (Auth::user()->isAbleTo('zoommeeting manage')) {
            $validator = \Validator::make($request->all(), [
                'zoom_account_id' => 'required|string',
                'zoom_client_id' => 'required|string',
                'zoom_client_secret' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $post = $request->except('_token');

            foreach ($post as $key => $value) {
                Setting::updateOrInsert(['key' => $key], ['value' => $value]);
            }

            comapnySettingCacheForget(); // optional: rename to globalSettingCacheForget if no longer company-specific
            return redirect()->back()->with('success', 'Zoom settings saved successfully.');
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function calender(Request $request)
    {
        if (Auth::user()->isAbleTo('zoommeeting manage')) {
            $zoomMeetings = ZoomMeeting::where('created_by', creatorId())
                ->where('workspace_id', getActiveWorkSpace())
                ->get();

            $calendar = [];
            foreach ($zoomMeetings as $zoomMeeting) {
                $calendar[] = [
                    'id' => $zoomMeeting->id,
                    'title' => company_Time_formate($zoomMeeting->start_date) . ' ' . $zoomMeeting->title,
                    'start' => date('Y-m-d', strtotime($zoomMeeting->start_date)),
                    'end' => $zoomMeeting->end_date,
                    'className' => 'event-primary',
                    'url' => route('zoom-meeting.show', $zoomMeeting->id),
                ];
            }

            $calendarData = json_encode($calendar);
            return view('zoom-meeting::calender', compact('calendar', 'calendarData', 'zoomMeetings'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
