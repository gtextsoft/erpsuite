<?php

namespace Modules\Hrm\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Attendance;
use Modules\Hrm\Entities\Branch;
use Modules\Hrm\Entities\Department;
use Modules\Hrm\Entities\IpRestrict;
use Modules\Hrm\Events\CreateMarkAttendance;
use Modules\Hrm\Events\DestroyMarkAttendance;
use Modules\Hrm\Events\UpdateBulkAttendance;
use Modules\Hrm\Events\UpdateMarkAttendance;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance manage')) {
            $currentWorkspace = getActiveWorkSpace();
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $attendances = Attendance::where('employee_id', Auth::user()->id)->where('workspace', getActiveWorkSpace());
                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween('date', [$start_date, $end_date]);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendances->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween('date', [$start_date, $end_date]);
                }
                $attendances = $attendances->get();
            } else {
                $employee = User::where('workspace_id', getActiveWorkSpace())
                    ->leftjoin('employees', 'users.id', '=', 'employees.user_id')
                    ->where('users.created_by', creatorId())->emp()
                    ->select('users.id');
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }
                $employee = $employee->get()->pluck('id');

                $attendances = Attendance::whereIn('employee_id', $employee)->where('workspace', getActiveWorkSpace())->with('employees');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween('date', [$start_date, $end_date]);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendances->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date   = date($year . '-' . $month . '-t');

                    $attendances->whereBetween('date', [$start_date, $end_date]);
                }

                $attendances = $attendances->get();
            }

            return view('hrm::attendance.index', compact('attendances', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('attendance create')) {
            $currentWorkspace = getActiveWorkSpace();
            $employees = User::where('workspace_id', $currentWorkspace)->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');
            return view('hrm::attendance.create', compact('employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $company_settings = getCompanyAllSetting();
            $startTime = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
            $endTime = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';

            // Get the employee from the current workspace
            $employee = User::find($request->employee_id);
            if (!$employee) {
                return redirect()->back()->with('error', __('Employee not found.'));
            }

            // Check if attendance already exists in the current workspace
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('workspace', getActiveWorkSpace())
                ->where('date', $request->date)
                ->where('clock_out', '00:00:00')
                ->first();

            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            }

            $date = $request->date;

            // Calculate late, early leaving, and overtime
            $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . ' ' . $startTime);
            $late = ($totalLateSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalLateSeconds / 3600), floor($totalLateSeconds / 60 % 60), floor($totalLateSeconds % 60));

            $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($request->clock_out);
            $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

            $overtime = '00:00:00';
            if (strtotime($request->clock_out) > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . ' ' . $endTime);
                $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalOvertimeSeconds / 60 % 60), floor($totalOvertimeSeconds % 60));
            }

            // Create attendance for the current workspace
            $employeeAttendance = new Attendance();
            $employeeAttendance->employee_id = $employee->id;
            $employeeAttendance->date = $request->date;
            $employeeAttendance->status = 'Present';
            $employeeAttendance->clock_in = $request->clock_in . ':00';
            $employeeAttendance->clock_out = $request->clock_out . ':00';
            $employeeAttendance->late = $late;
            $employeeAttendance->early_leaving = $earlyLeaving;
            $employeeAttendance->overtime = $overtime;
            $employeeAttendance->total_rest = '00:00:00';
            $employeeAttendance->workspace = getActiveWorkSpace();
            $employeeAttendance->created_by = \Auth::user()->id;
            $employeeAttendance->save();

            event(new CreateMarkAttendance($request, $employeeAttendance));

            // Sync attendance across all workspaces with the same email
            $usersWithSameEmail = User::where('email', $employee->email)
                ->where('id', '!=', $employee->id) // Exclude the current employee
                ->get();

            foreach ($usersWithSameEmail as $user) {
                $existingAttendance = Attendance::where('employee_id', $user->id)
                    ->where('workspace', $user->workspace_id)
                    ->where('date', $request->date)
                    ->first();

                if (!$existingAttendance) {
                    $syncedAttendance = new Attendance();
                    $syncedAttendance->employee_id = $user->id;
                    $syncedAttendance->date = $request->date;
                    $syncedAttendance->status = 'Present';
                    $syncedAttendance->clock_in = $request->clock_in . ':00';
                    $syncedAttendance->clock_out = $request->clock_out . ':00';
                    $syncedAttendance->late = $late;
                    $syncedAttendance->early_leaving = $earlyLeaving;
                    $syncedAttendance->overtime = $overtime;
                    $syncedAttendance->total_rest = '00:00:00';
                    $syncedAttendance->workspace = $user->workspace_id;
                    $syncedAttendance->created_by = \Auth::user()->id;
                    $syncedAttendance->save();

                    event(new CreateMarkAttendance($request, $syncedAttendance));
                }
            }

            return redirect()->route('attendance.index')->with('success', __('Employee attendance successfully created and synced across workspaces.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
        return view('hrm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('attendance edit')) {
            $currentWorkspace = getActiveWorkSpace();
            $attendance = Attendance::where('id', $id)->first();
            $employees = User::where('workspace_id', $currentWorkspace)->where('created_by', '=', creatorId())->emp()->get()->pluck('name', 'id');
            return view('hrm::attendance.edit', compact('attendance', 'employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);
        if (!$attendance) {
            return redirect()->back()->with('error', __('Attendance not found.'));
        }

        $employeeId = !empty($request->employee_id) ? $request->employee_id : $attendance->employee_id;
        $employee = User::find($employeeId);
        if (!$employee) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        $company_settings = getCompanyAllSetting();
        $startTime = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
        $endTime = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';

        if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            // Employee clock-out scenario
            if (!empty($company_settings['defult_timezone'])) {
                date_default_timezone_set($company_settings['defult_timezone']);
            }
            $date = date("Y-m-d");
            $time = date("H:i");

            $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - time();
            $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

            $overtime = '00:00:00';
            if (time() > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = time() - strtotime($date . ' ' . $endTime);
                $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));
            }

            $attendance->clock_out = $time;
            $attendance->early_leaving = $earlyLeaving;
            $attendance->overtime = $overtime;
            $attendance->save();

            event(new UpdateMarkAttendance($request, $attendance));

            // Sync clock-out across all workspaces with the same email
            $usersWithSameEmail = User::where('email', $employee->email)
                ->where('id', '!=', $employee->id)
                ->get();

            foreach ($usersWithSameEmail as $user) {
                $syncedAttendance = Attendance::where('employee_id', $user->id)
                    ->where('workspace', $user->workspace_id)
                    ->where('date', $date)
                    ->first();

                if ($syncedAttendance) {
                    $syncedAttendance->clock_out = $time;
                    $syncedAttendance->early_leaving = $earlyLeaving;
                    $syncedAttendance->overtime = $overtime;
                    $syncedAttendance->save();

                    event(new UpdateMarkAttendance($request, $syncedAttendance));
                }
            }

            return redirect()->back()->with('success', __('Employee Successfully Clocked Out and synced across workspaces.'));
        } else {
            // Admin update scenario
            if (!Auth::user()->isAbleTo('attendance edit')) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $date = $request->date;

            $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . ' ' . $startTime);
            $late = ($totalLateSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalLateSeconds / 3600), floor($totalLateSeconds / 60 % 60), floor($totalLateSeconds % 60));

            $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($request->clock_out);
            $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

            $overtime = '00:00:00';
            if (strtotime($request->clock_out) > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . ' ' . $endTime);
                $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalOvertimeSeconds / 60 % 60), floor($totalOvertimeSeconds % 60));
            }

            $attendance->employee_id = $employeeId;
            $attendance->date = $request->date;
            $attendance->clock_in = $request->clock_in;
            $attendance->clock_out = $request->clock_out;
            $attendance->late = $late;
            $attendance->early_leaving = $earlyLeaving;
            $attendance->overtime = $overtime;
            $attendance->total_rest = '00:00:00';
            $attendance->save();

            event(new UpdateMarkAttendance($request, $attendance));

            // Sync updates across all workspaces with the same email
            $usersWithSameEmail = User::where('email', $employee->email)
                ->where('id', '!=', $employee->id)
                ->get();

            foreach ($usersWithSameEmail as $user) {
                $syncedAttendance = Attendance::where('employee_id', $user->id)
                    ->where('workspace', $user->workspace_id)
                    ->where('date', $request->date)
                    ->first();

                if ($syncedAttendance) {
                    $syncedAttendance->clock_in = $request->clock_in;
                    $syncedAttendance->clock_out = $request->clock_out;
                    $syncedAttendance->late = $late;
                    $syncedAttendance->early_leaving = $earlyLeaving;
                    $syncedAttendance->overtime = $overtime;
                    $syncedAttendance->total_rest = '00:00:00';
                    $syncedAttendance->save();

                    event(new UpdateMarkAttendance($request, $syncedAttendance));
                } else {
                    $syncedAttendance = new Attendance();
                    $syncedAttendance->employee_id = $user->id;
                    $syncedAttendance->date = $request->date;
                    $syncedAttendance->status = 'Present';
                    $syncedAttendance->clock_in = $request->clock_in;
                    $syncedAttendance->clock_out = $request->clock_out;
                    $syncedAttendance->late = $late;
                    $syncedAttendance->early_leaving = $earlyLeaving;
                    $syncedAttendance->overtime = $overtime;
                    $syncedAttendance->total_rest = '00:00:00';
                    $syncedAttendance->workspace = $user->workspace_id;
                    $syncedAttendance->created_by = \Auth::user()->id;
                    $syncedAttendance->save();

                    event(new CreateMarkAttendance($request, $syncedAttendance));
                }
            }

            return redirect()->route('attendance.index')->with('success', __('Employee attendance successfully updated and synced across workspaces.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Attendance $attendance)
    {
        if (Auth::user()->isAbleTo('attendance delete')) {
            if ($attendance->workspace == getActiveWorkSpace()) {
                event(new DestroyMarkAttendance($attendance));
                $attendance->delete();
                return redirect()->route('attendance.index')->with('success', __('Attendance successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function BulkAttendance(Request $request)
    {
        if (Auth::user()->isAbleTo('bulk attendance manage')) {
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');

            $department = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = User::where('workspace_id', getActiveWorkSpace())
                    ->leftjoin('employees', 'users.id', '=', 'employees.user_id')
                    ->where('users.created_by', creatorId())->emp()
                    ->where('employees.branch_id', $request->branch)
                    ->where('employees.department_id', $request->department)
                    ->where('employees.dob', '<=', $request->date)
                    ->select('users.*', 'users.id as ID', 'employees.*', 'users.name as name', 'users.email as email', 'users.id as id')
                    ->get();
            }
            return view('hrm::attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function BulkAttendanceData(Request $request)
    {
        if (Auth::user()->isAbleTo('bulk attendance manage')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $company_settings = getCompanyAllSetting();
                $startTime = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
                $endTime = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';
                $employees = $request->employee_id;
                $atte = [];

                foreach ($employees as $employee) {
                    $present = 'present-' . $employee;
                    $in = 'in-' . $employee;
                    $out = 'out-' . $employee;
                    $atte[] = $present;
                    if ($request->$present == 'on') {
                        $in = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        $totalLateSeconds = strtotime($in) - strtotime($startTime);
                        $late = ($totalLateSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalLateSeconds / 3600), floor($totalLateSeconds / 60 % 60), floor($totalLateSeconds % 60));

                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($out);
                        $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

                        $overtime = '00:00:00';
                        if (strtotime($out) > strtotime($endTime)) {
                            $totalOvertimeSeconds = strtotime($out) - strtotime($endTime);
                            $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalOvertimeSeconds / 60 % 60), floor($totalOvertimeSeconds % 60));
                        }

                        $attendance = Attendance::where('employee_id', '=', $employee)->where('workspace', getActiveWorkSpace())->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance = new Attendance();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by = \Auth::user()->id;
                            $employeeAttendance->workspace = getActiveWorkSpace();
                        }
                        $employeeAttendance->date = $request->date;
                        $employeeAttendance->status = 'Present';
                        $employeeAttendance->clock_in = $in;
                        $employeeAttendance->clock_out = $out;
                        $employeeAttendance->late = $late;
                        $employeeAttendance->early_leaving = $earlyLeaving;
                        $employeeAttendance->overtime = $overtime;
                        $employeeAttendance->total_rest = '00:00:00';
                        $employeeAttendance->save();

                        event(new UpdateBulkAttendance($request, $employeeAttendance));

                        // Sync bulk attendance across workspaces
                        $user = User::find($employee);
                        $usersWithSameEmail = User::where('email', $user->email)
                            ->where('id', '!=', $user->id)
                            ->get();

                        foreach ($usersWithSameEmail as $syncUser) {
                            $syncedAttendance = Attendance::where('employee_id', $syncUser->id)
                                ->where('workspace', $syncUser->workspace_id)
                                ->where('date', $request->date)
                                ->first();

                            if (!$syncedAttendance) {
                                $syncedAttendance = new Attendance();
                                $syncedAttendance->employee_id = $syncUser->id;
                                $syncedAttendance->created_by = \Auth::user()->id;
                                $syncedAttendance->workspace = $syncUser->workspace_id;
                            }
                            $syncedAttendance->date = $request->date;
                            $syncedAttendance->status = 'Present';
                            $syncedAttendance->clock_in = $in;
                            $syncedAttendance->clock_out = $out;
                            $syncedAttendance->late = $late;
                            $syncedAttendance->early_leaving = $earlyLeaving;
                            $syncedAttendance->overtime = $overtime;
                            $syncedAttendance->total_rest = '00:00:00';
                            $syncedAttendance->save();

                            event(new UpdateBulkAttendance($request, $syncedAttendance));
                        }
                    } else {
                        $attendance = Attendance::where('employee_id', '=', $employee)->where('workspace', getActiveWorkSpace())->where('date', '=', $request->date)->first();

                        if (!empty($attendance)) {
                            $employeeAttendance = $attendance;
                        } else {
                            $employeeAttendance = new Attendance();
                            $employeeAttendance->employee_id = $employee;
                            $employeeAttendance->created_by = \Auth::user()->id;
                            $employeeAttendance->workspace = getActiveWorkSpace();
                        }
                        $employeeAttendance->status = 'Leave';
                        $employeeAttendance->date = $request->date;
                        $employeeAttendance->clock_in = '00:00:00';
                        $employeeAttendance->clock_out = '00:00:00';
                        $employeeAttendance->late = '00:00:00';
                        $employeeAttendance->early_leaving = '00:00:00';
                        $employeeAttendance->overtime = '00:00:00';
                        $employeeAttendance->total_rest = '00:00:00';
                        $employeeAttendance->save();

                        event(new UpdateBulkAttendance($request, $employeeAttendance));
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created and synced across workspaces.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

   public function attendance(Request $request)
{
    $company_settings = getCompanyAllSetting();
    
    // Check IP restriction if enabled
    if (!empty($company_settings['ip_restrict']) && $company_settings['ip_restrict'] == 'on') {
        $userIp = request()->ip();
        $ip = IpRestrict::where('created_by', creatorId())
            ->where('workspace', getActiveWorkSpace())
            ->whereIn('ip', [$userIp])
            ->first();

        if (empty($ip)) {
            return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
        }
    }

    $employeeId = \Auth::user()->id;
    $date = date("Y-m-d");
    $time = date("H:i");
    
    // Set default timezone if configured
    if (!empty($company_settings['defult_timezone'])) {
        date_default_timezone_set($company_settings['defult_timezone']);
    }

    $startTime = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
    $endTime = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';

    // Check for today's attendance record
    $todayAttendance = Attendance::where('employee_id', $employeeId)
        ->where('workspace', getActiveWorkSpace())
        ->where('date', $date)
        ->first();

    // If an attendance record exists and clock_out is not set, perform clock-out
    if ($todayAttendance && $todayAttendance->clock_out == '00:00:00') {
        $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($time);
        $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

        $overtime = '00:00:00';
        if (strtotime($time) > strtotime($date . ' ' . $endTime)) {
            $totalOvertimeSeconds = strtotime($time) - strtotime($date . ' ' . $endTime);
            $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalOvertimeSeconds / 60 % 60), floor($totalOvertimeSeconds % 60));
        }

        $todayAttendance->clock_out = $time . ':00';
        $todayAttendance->early_leaving = $earlyLeaving;
        $todayAttendance->overtime = $overtime;
        $todayAttendance->save();

        // Sync clock-out across workspaces with the same email
        $employee = User::find($employeeId);
        $usersWithSameEmail = User::where('email', $employee->email)
            ->where('id', '!=', $employee->id)
            ->get();

        foreach ($usersWithSameEmail as $user) {
            $syncedAttendance = Attendance::where('employee_id', $user->id)
                ->where('workspace', $user->workspace_id)
                ->where('date', $date)
                ->where('clock_out', '00:00:00')
                ->first();

            if ($syncedAttendance) {
                $syncedAttendance->clock_out = $time . ':00';
                $syncedAttendance->early_leaving = $earlyLeaving;
                $syncedAttendance->overtime = $overtime;
                $syncedAttendance->save();
            }
        }

        return redirect()->back()->with('success', __('Employee Successfully Clocked Out and synced across workspaces.'));
    }

    // If no attendance record exists for today, perform clock-in
    if (!$todayAttendance) {
        $totalLateSeconds = strtotime($time) - strtotime($date . ' ' . $startTime);
        $late = ($totalLateSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalLateSeconds / 3600), floor($totalLateSeconds / 60 % 60), floor($totalLateSeconds % 60));

        $employeeAttendance = new Attendance();
        $employeeAttendance->employee_id = $employeeId;
        $employeeAttendance->date = $date;
        $employeeAttendance->status = 'Present';
        $employeeAttendance->clock_in = $time . ':00';
        $employeeAttendance->clock_out = '00:00:00';
        $employeeAttendance->late = $late;
        $employeeAttendance->early_leaving = '00:00:00';
        $employeeAttendance->overtime = '00:00:00';
        $employeeAttendance->total_rest = '00:00:00';
        $employeeAttendance->created_by = creatorId();
        $employeeAttendance->workspace = getActiveWorkSpace();
        $employeeAttendance->save();

        // Sync clock-in across workspaces with the same email
        $employee = User::find($employeeId);
        $usersWithSameEmail = User::where('email', $employee->email)
            ->where('id', '!=', $employee->id)
            ->get();

        foreach ($usersWithSameEmail as $user) {
            $syncedAttendance = Attendance::where('employee_id', $user->id)
                ->where('workspace', $user->workspace_id)
                ->where('date', $date)
                ->first();

            if (!$syncedAttendance) {
                $syncedAttendance = new Attendance();
                $syncedAttendance->employee_id = $user->id;
                $syncedAttendance->date = $date;
                $syncedAttendance->status = 'Present';
                $syncedAttendance->clock_in = $time . ':00';
                $syncedAttendance->clock_out = '00:00:00';
                $syncedAttendance->late = $late;
                $syncedAttendance->early_leaving = '00:00:00';
                $syncedAttendance->overtime = '00:00:00';
                $syncedAttendance->total_rest = '00:00:00';
                $syncedAttendance->created_by = creatorId();
                $syncedAttendance->workspace = $user->workspace_id;
                $syncedAttendance->save();
            }
        }

        return redirect()->back()->with('success', __('Employee Successfully Clocked In and synced across workspaces.'));
    }

    return redirect()->back()->with('error', __('Attendance already recorded for today.'));
}

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            return view('hrm::attendance.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            session_start();

            $error = '';
            $html = '';
            if ($request->hasFile('file')) {
                $file_array = explode(".", $request->file->getClientOriginalName());
                $extension = end($file_array);

                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');
                    $file_header = fgetcsv($file_data);

                    $html .= '<table class="table table-bordered"><tr>';
                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                            <th>
                                <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="email">Employee Email</option>
                                    <option value="date">Date</option>
                                    <option value="clock_in">Clock in</option>
                                    <option value="clock_out">Clock out</option>
                                </select>
                            </th>';
                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;
                        $html .= '<tr>';
                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }
                        $html .= '</tr>';
                        $temp_data[] = $row;
                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {
                $error = 'Please Select File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        } else {
            $output = array(
                'error' => 'Permission denied.',
                'output' => '',
            );
            return json_encode($output);
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            return view('hrm::attendance.import_modal');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function AttendanceImportdata(Request $request)
    {
        if (Auth::user()->isAbleTo('attendance import')) {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];
            $company_settings = getCompanyAllSetting();

            foreach ($file_data as $key => $row) {
                $startTime = !empty($company_settings['company_start_time']) ? $company_settings['company_start_time'] : '09:00';
                $endTime = !empty($company_settings['company_end_time']) ? $company_settings['company_end_time'] : '18:00';

                $employee = User::where('workspace_id', getActiveWorkSpace())->where('created_by', '=', creatorId())->where('email', $row[$request->email])->emp()->first();
                $attendance = null;
                if (!empty($employee)) {
                    $attendance = Attendance::where('employee_id', '=', $employee->id)->where('workspace', getActiveWorkSpace())->where('date', '=', $row[$request->date])->first();
                }
                if (empty($attendance) && !empty($employee)) {
                    try {
                        $date = $row[$request->date];
                        $totalLateSeconds = strtotime($row[$request->clock_in]) - strtotime($date . ' ' . $startTime);
                        $late = ($totalLateSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalLateSeconds / 3600), floor($totalLateSeconds / 60 % 60), floor($totalLateSeconds % 60));

                        $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($row[$request->clock_out]);
                        $earlyLeaving = ($totalEarlyLeavingSeconds < 0) ? '0:00:00' : sprintf('%02d:%02d:%02d', floor($totalEarlyLeavingSeconds / 3600), floor($totalEarlyLeavingSeconds / 60 % 60), floor($totalEarlyLeavingSeconds % 60));

                        $overtime = '00:00:00';
                        if (strtotime($row[$request->clock_out]) > strtotime($date . ' ' . $endTime)) {
                            $totalOvertimeSeconds = strtotime($row[$request->clock_out]) - strtotime($date . ' ' . $endTime);
                            $overtime = sprintf('%02d:%02d:%02d', floor($totalOvertimeSeconds / 3600), floor($totalOvertimeSeconds / 60 % 60), floor($totalOvertimeSeconds % 60));
                        }

                        $employeeAttendance = new Attendance();
                        $employeeAttendance->employee_id = $employee->id;
                        $employeeAttendance->date = $row[$request->date];
                        $employeeAttendance->status = 'Present';
                        $employeeAttendance->clock_in = $row[$request->clock_in] . ':00';
                        $employeeAttendance->clock_out = $row[$request->clock_out] . ':00';
                        $employeeAttendance->late = $late;
                        $employeeAttendance->early_leaving = $earlyLeaving;
                        $employeeAttendance->overtime = $overtime;
                        $employeeAttendance->total_rest = '00:00:00';
                        $employeeAttendance->workspace = getActiveWorkSpace();
                        $employeeAttendance->created_by = creatorId();
                        $employeeAttendance->save();

                        // Sync imported attendance across workspaces
                        $usersWithSameEmail = User::where('email', $employee->email)
                            ->where('id', '!=', $employee->id)
                            ->get();

                        foreach ($usersWithSameEmail as $user) {
                            $syncedAttendance = Attendance::where('employee_id', $user->id)
                                ->where('workspace', $user->workspace_id)
                                ->where('date', $row[$request->date])
                                ->first();

                            if (!$syncedAttendance) {
                                $syncedAttendance = new Attendance();
                                $syncedAttendance->employee_id = $user->id;
                                $syncedAttendance->date = $row[$request->date];
                                $syncedAttendance->status = 'Present';
                                $syncedAttendance->clock_in = $row[$request->clock_in] . ':00';
                                $syncedAttendance->clock_out = $row[$request->clock_out] . ':00';
                                $syncedAttendance->late = $late;
                                $syncedAttendance->early_leaving = $earlyLeaving;
                                $syncedAttendance->overtime = $overtime;
                                $syncedAttendance->total_rest = '00:00:00';
                                $syncedAttendance->workspace = $user->workspace_id;
                                $syncedAttendance->created_by = creatorId();
                                $syncedAttendance->save();
                            }
                        }
                    } catch (\Exception $e) {
                        $flag = 1;
                        $html .= '<tr>';
                        $html .= '<td>' . $row[$request->email] . '</td>';
                        $html .= '<td>' . $row[$request->date] . '</td>';
                        $html .= '<td>' . $row[$request->clock_in] . '</td>';
                        $html .= '<td>' . $row[$request->clock_out] . '</td>';
                        $html .= '</tr>';
                    }
                } else {
                    $flag = 1;
                    $html .= '<tr>';
                    $html .= '<td>' . $row[$request->email] . '</td>';
                    $html .= '<td>' . $row[$request->date] . '</td>';
                    $html .= '<td>' . $row[$request->clock_in] . '</td>';
                    $html .= '<td>' . $row[$request->clock_out] . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</table><br />';
            if ($flag == 1) {
                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => 'Data Imported and Synced Successfully',
                ]);
            }
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Permission denied.',
            ]);
        }
    }
}