<?php

namespace Modules\Hrm\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Employee;
use Modules\Hrm\Entities\Leave;
use Modules\Hrm\Entities\LeaveType;
use Modules\Hrm\Events\CreateLeave;
use Modules\Hrm\Events\DestroyLeave;
use Modules\Hrm\Events\LeaveStatus;
use Modules\Hrm\Events\UpdateLeave;

class LeaveController extends Controller
{
    
    
public function index()
{
    if (Auth::user()->isAbleTo('leave manage')) {
        if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
            // Admin/HR: Show all leaves
            $leaves = Leave::where('workspace', getActiveWorkSpace())
                           ->with('leaveType', 'approver', 'user')
                           ->orderBy('id', 'desc')
                           ->get();
        } else {
            // Normal users: Show their own leaves AND leaves they are assigned to approve
            $leaves = Leave::where('workspace', getActiveWorkSpace())
                           ->where(function ($query) {
                               $query->where('user_id', Auth::user()->id) // Their own leaves
                                     ->orWhere('approved_by', Auth::user()->id); // Leaves they approve
                           })
                           ->with('leaveType', 'approver', 'user')
                           ->orderBy('id', 'desc')
                           ->get();
        }
        return view('hrm::leave.index', compact('leaves'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}


// public function index()
// {
//     if (Auth::user()->isAbleTo('leave manage')) {
//         if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
//             $leaves = Leave::where('user_id', '=', Auth::user()->id)
//                           ->where('leaves.workspace', getActiveWorkSpace())
//                           ->with('leaveType', 'approver', 'user')
//                           ->orderBy('id', 'desc')
//                           ->get();
//         } else {
//             $leaves = Leave::where('leaves.workspace', getActiveWorkSpace())
//                           ->where(function ($query) {
//                               $query->where('leaves.created_by', creatorId())
//                                      ->orWhere('leaves.approved_by', Auth::user()->id);
//                           })
//                           ->with('leaveType', 'approver', 'user')
//                           ->orderBy('id', 'desc')
//                           ->get();
//         }
//         return view('hrm::leave.index', compact('leaves'));
//     } else {
//         return redirect()->back()->with('error', __('Permission denied.'));
//     }
// }



    public function create()
    {
        if (Auth::user()->isAbleTo('leave create')) {
            if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $employees = Employee::where('user_id', '=', Auth::user()->id)->where('workspace', getActiveWorkSpace())->first();
            } else {
                $employees = Employee::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
            }
            $leavetypes = LeaveType::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get();
            $users = User::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('hrm::leave.create', compact('employees', 'leavetypes', 'users'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('leave create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'leave_type_id' => 'required',
                    'start_date' => 'required|after:yesterday',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'approved_by' => 'required|exists:users,id',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $leave_type = LeaveType::find($request->leave_type_id);
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));

            $leave = new Leave();
            if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                $employee = Employee::where('id', '=', $request->employee_id)->first();
                $leave->employee_id = $request->employee_id;
                $leave->user_id = $employee->user_id;
            } else {
                $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
                if (!empty($employee)) {
                    $leave->user_id = Auth::user()->id;
                    $leave->employee_id = $employee->id;
                } else {
                    return redirect()->back()->with('error', __('Apologies, the employee data is currently unavailable. Please provide the necessary employee details.'));
                }
            }

            $date = AnnualLeaveCycle();
            $leaves_used = Leave::where('employee_id', '=', $leave->employee_id)
                                ->where('leave_type_id', $leave_type->id)
                                ->where('status', 'Approved')
                                ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                                ->sum('total_leave_days');

            $leaves_pending = Leave::where('employee_id', '=', $leave->employee_id)
                                  ->where('leave_type_id', $leave_type->id)
                                  ->where('status', 'Pending')
                                  ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                                  ->sum('total_leave_days');

            $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $return = $leave_type->days - $leaves_used;

            if ($total_leave_days > $return) {
                return redirect()->back()->with('error', __('You are not eligible for leave.'));
            }
            if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
            }

            if ($leave_type->days >= $total_leave_days) {
                $leave->leave_type_id    = $request->leave_type_id;
                $leave->applied_on       = date('Y-m-d');
                $leave->start_date       = $request->start_date;
                $leave->end_date         = $request->end_date;
                $leave->total_leave_days = $total_leave_days;
                $leave->leave_reason     = $request->leave_reason;
                $leave->status           = 'Pending';
                $leave->approver_status  = 'Pending';
                $leave->workspace        = getActiveWorkSpace();
                $leave->created_by       = creatorId();
                $leave->approved_by      = $request->approved_by;

                $leave->save();

                event(new CreateLeave($request, $leave));

                return redirect()->route('leave.index')->with('success', __('Leave successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

 public function recommend($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->approved_by != Auth::user()->id) {
            return redirect()->back()->with('error', __('You are not authorized to recommend this leave.'));
        }

        if ($leave->approver_status != 'Pending') {
            return redirect()->back()->with('error', __('This leave has already been reviewed by the approver.'));
        }

        return view('hrm::leave.recommend', compact('leave'));
    }

    public function recommendStore(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->approved_by != Auth::user()->id) {
            return redirect()->back()->with('error', __('You are not authorized to recommend this leave.'));
        }

        $validator = \Validator::make($request->all(), [
            'recommender_remark' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $leave->approver_status = 'Recommended';
        $leave->recommender_remark = $request->recommender_remark;
        $leave->save();

        return redirect()->route('leave.index')->with('success', __('Leave recommended successfully.'));
    }

    public function reject($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->approved_by != Auth::user()->id) {
            return redirect()->back()->with('error', __('You are not authorized to reject this leave.'));
        }

        if ($leave->approver_status != 'Pending') {
            return redirect()->back()->with('error', __('This leave has already been reviewed by the approver.'));
        }

        return view('hrm::leave.reject', compact('leave'));
    }

    public function rejectStore(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->approved_by != Auth::user()->id) {
            return redirect()->back()->with('error', __('You are not authorized to reject this leave.'));
        }

        $validator = \Validator::make($request->all(), [
            'recommender_remark' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $leave->approver_status = 'Rejected';
        $leave->recommender_remark = $request->recommender_remark;
        $leave->save();

        return redirect()->route('leave.index')->with('success', __('Leave rejected successfully.'));
    }

    public function approve($id)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave = Leave::findOrFail($id);

            if ($leave->approver_status == 'Pending') {
                return redirect()->back()->with('error', __('This leave requires initial approval first.'));
            }

            return view('hrm::leave.approve', compact('leave'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function approveStore(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave = Leave::findOrFail($id);

            if ($leave->approver_status == 'Pending') {
                return redirect()->back()->with('error', __('This leave requires initial approval first.'));
            }

            $validator = \Validator::make($request->all(), [
                'final_remark' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $leave->status = 'Approved';
            $leave->final_remark = $request->final_remark;
            $leave->save();

            return redirect()->route('leave.index')->with('success', __('Leave approved successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function decline($id)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave = Leave::findOrFail($id);

            if ($leave->approver_status == 'Pending') {
                return redirect()->back()->with('error', __('This leave requires initial approval first.'));
            }

            return view('hrm::leave.decline', compact('leave'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function declineStore(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave = Leave::findOrFail($id);

            if ($leave->approver_status == 'Pending') {
                return redirect()->back()->with('error', __('This leave requires initial approval first.'));
            }

            $validator = \Validator::make($request->all(), [
                'final_remark' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $leave->status = 'Declined';
            $leave->final_remark = $request->final_remark;
            $leave->save();

            return redirect()->route('leave.index')->with('success', __('Leave declined successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show($id)
    {
        return redirect()->back();
    }

    public function edit(Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave edit')) {
            if ($leave->workspace == getActiveWorkSpace()) {
                if (!in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                    $employees = Employee::where('user_id', '=', Auth::user()->id)->where('workspace', getActiveWorkSpace())->first();
                } else {
                    $employees = Employee::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get()->pluck('name', 'id');
                }
                $leavetypes = LeaveType::where('workspace', getActiveWorkSpace())->where('created_by', '=', creatorId())->get();

                return view('hrm::leave.edit', compact('leave', 'employees', 'leavetypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave edit')) {
            if ($leave->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'leave_type_id' => 'required',
                        'start_date' => 'required|date',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'remark' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $leave_type = LeaveType::find($request->leave_type_id);
                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D'));

                $date = AnnualLeaveCycle();
                $leaves_used = Leave::whereNotIn('id', [$leave->id])
                                    ->where('employee_id', '=', $leave->employee_id)
                                    ->where('leave_type_id', $leave_type->id)
                                    ->where('status', 'Approved')
                                    ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                                    ->sum('total_leave_days');

                $leaves_pending = Leave::whereNotIn('id', [$leave->id])
                                      ->where('employee_id', '=', $leave->employee_id)
                                      ->where('leave_type_id', $leave_type->id)
                                      ->where('status', 'Pending')
                                      ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                                      ->sum('total_leave_days');

                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                $return = $leave_type->days - $leaves_used;

                if ($total_leave_days > $return) {
                    return redirect()->back()->with('error', __('You are not eligible add more leave days.'));
                }
                if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                    return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
                }

                if ($leave_type->days >= $total_leave_days) {
                    if (in_array(Auth::user()->type, Auth::user()->not_emp_type)) {
                        $employee = Employee::where('id', '=', $request->employee_id)->first();
                        $leave->employee_id = $request->employee_id;
                        $leave->user_id = $employee->user_id;
                    } else {
                        $employee = Employee::where('user_id', '=', Auth::user()->id)->first();
                        $leave->user_id = Auth::user()->id;
                        $leave->employee_id = $employee->id;
                    }
                    if (!empty($request->status)) {
                        $leave->status = $request->status;
                    }
                    $leave->start_date       = $request->start_date;
                    $leave->end_date         = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason     = $request->leave_reason;
                    $leave->remark           = $request->remark;

                    $leave->save();
                    event(new UpdateLeave($request, $leave));
                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $leave_type->days . "  days please make sure your selected days is under " . $leave_type->days . ' days.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

       public function destroy(Leave $leave)
    {
        if (Auth::user()->isAbleTo('leave delete')) {
            if ($leave->created_by == creatorId() &&  $leave->workspace  == getActiveWorkSpace() && $leave->status == 'Pending') {
                event(new DestroyLeave($leave));
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jsoncount(Request $request)
    {
        $date = AnnualLeaveCycle();

        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))->leftjoin(
            'leaves',
            function ($join) use ($request, $date) {
                $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                $join->where('leaves.employee_id', '=', $request->employee_id);
                $join->where('leaves.status', '=', 'Approved');
                $join->whereBetween('leaves.created_at', [$date['start_date'], $date['end_date']]);
            }
        )->where('leave_types.created_by', '=', creatorId())->groupBy('leave_types.id')->get();
        return $leave_counts;
    }

    public function action($id)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave     = Leave::find($id);
            $employee  = User::find($leave->user_id);
            $leavetype = LeaveType::find($leave->leave_type_id);
            return view('hrm::leave.action', compact('employee', 'leavetype', 'leave'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function changeaction(Request $request)
    {
        if (Auth::user()->isAbleTo('leave approver manage')) {
            $leave = Leave::find($request->leave_id);
            $leave->status = $request->status;
            if ($leave->status == 'Approved') {
                $startDate               = new \DateTime($leave->start_date);
                $endDate                 = new \DateTime($leave->end_date);
                $endDate->add(new \DateInterval('P1D'));
                $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                $leave->total_leave_days = $total_leave_days;
            }
            $leave->save();
            event(new LeaveStatus($leave));
            $company_settings = getCompanyAllSetting();
            if (!empty($company_settings['Leave Status']) && $company_settings['Leave Status']  == true) {
                $User     = User::where('id', $leave->user_id)->where('workspace_id', '=',  getActiveWorkSpace())->first();
                $uArr = [
                    'leave_email' => $User->email,
                    'leave_status_name' => $User->name,
                    'leave_status' => $request->status,
                    'leave_reason' => $leave->leave_reason,
                    'leave_start_date' => $leave->start_date,
                    'leave_end_date' => $leave->end_date,
                    'total_leave_days' => $leave->total_leave_days,
                ];
                try {

                    $resp = EmailTemplate::sendEmailTemplate('Leave Status', [$User->email], $uArr);
                } catch (\Exception $e) {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            return redirect()->back()->with('success', __('Leave status successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
