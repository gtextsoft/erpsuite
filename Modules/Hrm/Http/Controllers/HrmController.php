<?php

namespace Modules\Hrm\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Announcement;
use Modules\Hrm\Entities\Attendance;
use Modules\Hrm\Entities\Employee;
use Modules\Hrm\Entities\ExperienceCertificate;
use Modules\Hrm\Entities\Holiday; // Corrected import
use Modules\Hrm\Entities\Event;   // Corrected import
use Modules\Hrm\Entities\IpRestrict;
use Modules\Hrm\Entities\JoiningLetter;
use Modules\Hrm\Entities\NOC;

class HrmController extends Controller
{
    public function __construct()
    {
        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function index(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->isAbleTo('hrm dashboard manage')) {
                $user = Auth::user();
                $events = [];
                $holidays = Holiday::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());
                if (!empty($request->date)) {
                    $date_range = explode(' to ', $request->date);
                    $holidays->where('start_date', '>=', $date_range[0]);
                    $holidays->where('end_date', '<=', $date_range[1]);
                }
                $holidays = $holidays->get();
                foreach ($holidays as $holiday) {
                    $data = [
                        'title' => $holiday->occasion,
                        'start' => $holiday->start_date,
                        'end' => $holiday->end_date,
                        'className' => 'event-danger'
                    ];
                    array_push($events, $data);
                }
                $hrm_events = Event::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace());
                if (!empty($request->date)) {
                    $date_range = explode(' to ', $request->date);
                    $hrm_events->where('start_date', '>=', $date_range[0]);
                    $hrm_events->where('end_date', '<=', $date_range[1]);
                }
                $hrm_events = $hrm_events->get();
                foreach ($hrm_events as $hrm_event) {
                    $data = [
                        'id'    => $hrm_event->id,
                        'title' => $hrm_event->title,
                        'start' => $hrm_event->start_date,
                        'end' => $hrm_event->end_date,
                        'className' => $hrm_event->color
                    ];
                    array_push($events, $data);
                }

                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $emp = Employee::where('user_id', '=', $user->id)->first();
                    if (!empty($emp)) {
                        $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)
                            ->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
                            ->where('announcement_employees.employee_id', '=', $emp->id)
                            ->orWhere(function ($q) {
                                $q->where('announcements.department_id', 0)->where('announcements.employee_id', 0);
                            })->get();
                    } else {
                        $announcements = [];
                    }

                    $date = date("Y-m-d");
                    $time = date("H:i:s");
                    $employeeAttendance = Attendance::orderBy('id', 'desc')
                        ->where('employee_id', '=', Auth::user()->id)
                        ->where('date', '=', $date)
                        ->first();
                    $company_settings = getCompanyAllSetting();
                    $officeTime['startTime'] = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
                    $officeTime['endTime'] = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';

                    return view('hrm::dashboard.dashboard', compact('announcements', 'events', 'employeeAttendance', 'officeTime'));
                } else {
                    $announcements = Announcement::orderBy('announcements.id', 'desc')
                        ->take(5)
                        ->where('workspace', getActiveWorkSpace())
                        ->get();

                    $emp = User::where('created_by', '=', Auth::user()->id)
                        ->emp()
                        ->where('workspace_id', getActiveWorkSpace())
                        ->get()
                        ->toArray();
                    $countEmployee = count($emp);
                    $emp_id = array_column($emp, 'id');

                    $user = User::whereNotIn('id', $emp_id)
                        ->where('created_by', '=', Auth::user()->id)
                        ->where('workspace_id', getActiveWorkSpace())
                        ->get();
                    $countUser = count($user);

                    $currentDate = date('Y-m-d'); // April 11, 2025, based on your provided date

                    // Fetch all employees and their attendance status for today
                    $employees = User::where('created_by', '=', Auth::user()->id)
                        ->emp()
                        ->where('workspace_id', getActiveWorkSpace())
                        ->select('id', 'name')
                        ->get();

                    $attendances = Attendance::where('date', $currentDate)
                        ->where('workspace', getActiveWorkSpace())
                        ->get()
                        ->keyBy('employee_id');

                    $clockedIn = [];
                    $notClockedIn = [];

                    foreach ($employees as $employee) {
                        if (isset($attendances[$employee->id])) {
                            $attendance = $attendances[$employee->id];
                            $clockedIn[] = [
                                'id' => $employee->id,
                                'name' => $employee->name,
                                'clock_in' => $attendance->clock_in,
                                'clock_out' => $attendance->clock_out === '00:00:00' ? 'Still Clocked In' : $attendance->clock_out,
                            ];
                        } else {
                            $notClockedIn[] = [
                                'id' => $employee->id,
                                'name' => $employee->name,
                            ];
                        }
                    }

                    return view('hrm::dashboard.dashboard', compact(
                        'announcements', 'countEmployee', 'events', 'countUser', 'clockedIn', 'notClockedIn', 'currentDate'
                    ));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // Other methods remain unchanged
    public function create()
    {
        return view('hrm::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return redirect()->back();
        return view('hrm::show');
    }

    public function edit($id)
    {
        return view('hrm::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function joiningletterupdate($lang, Request $request)
    {
        $user = JoiningLetter::updateOrCreate(['lang' =>   $lang, 'created_by' =>  \Auth::user()->id], ['content' => $request->joining_content, 'workspace' => getActiveWorkSpace()]);
        return redirect()->back()->with('success', __('Joing Letter successfully saved.'));
    }

    public function experienceCertificateupdate($lang, Request $request)
    {
        $user = ExperienceCertificate::updateOrCreate(['lang' =>   $lang, 'created_by' =>  \Auth::user()->id], ['content' => $request->experience_content, 'workspace' => getActiveWorkSpace()]);
        return redirect()->back()->with('success', __('Experience Certificate successfully saved.'));
    }

    public function NOCupdate($lang, Request $request)
    {
        $user = NOC::updateOrCreate(['lang' =>   $lang, 'created_by' =>  \Auth::user()->id], ['content' => $request->noc_content, 'workspace' => getActiveWorkSpace()]);
        return redirect()->back()->with('success', __('NOC successfully saved.'));
    }

    public function setting(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'employee_prefix' => 'required',
                'company_start_time' => 'required',
                'company_end_time' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        } else {
            $post = $request->all();
            if (!isset($request->ip_restrict)) {
                $post['ip_restrict'] = 'off';
            }
            unset($post['_token']);
            foreach ($post as $key => $value) {
                $data = [
                    'key' => $key,
                    'workspace' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ];
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            comapnySettingCacheForget();
            return redirect()->back()->with('success', 'HRM setting save sucessfully.');
        }
    }

    public function createIp()
    {
        if (Auth::user()->isAbleTo('ip restrict create')) {
            return view('hrm::restrict_ip.create');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function storeIp(Request $request)
    {
    }

    public function editIp($id)
    {
        if (Auth::user()->isAbleTo('ip restrict edit')) {
            $ip = IpRestrict::find($id);
            return view('hrm::restrict_ip.edit', compact('ip'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateIp(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('ip restrict edit')) {
            if (\Auth::user()->type == 'company') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'ip' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $ip = IpRestrict::find($id);
                $ip->ip = $request->ip;
                $ip->save();
                return redirect()->back()->with('success', __('IP successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyIp($id)
    {
        if (Auth::user()->isAbleTo('ip restrict delete')) {
            if (\Auth::user()->type == 'company') {
                $ip = IpRestrict::find($id);
                $ip->delete();
                return redirect()->back()->with('success', __('IP successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}