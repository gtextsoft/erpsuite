<?php

namespace Modules\Hrm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Hrm\Entities\Requisition;

class RequisitionController extends Controller
{
    public function index()
    {
        $requisitions = Requisition::where('employee_id', Auth::id())
            ->orWhere('first_approver_id', Auth::id())
            ->orWhere('gcoo_id', Auth::id())
            ->orWhere('accountant_id', Auth::id())
            ->get();

        return view('hrm::requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        try {
            // Fetch users excluding the current user and those with gcoo or accountant roles
            $approvers = User::where('id', '!=', Auth::id())
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['gcoo', 'accountant']);
                })
                ->pluck('name', 'id');

            // Log the result for debugging
            \Log::info('Approvers fetched for requisition create', [
                'user_id' => Auth::id(),
                'approvers_count' => $approvers->count(),
                'approvers' => $approvers->toArray(),
            ]);

            // If no approvers are found, set an empty array
            if ($approvers->isEmpty()) {
                \Log::warning('No approvers found for requisition create', ['user_id' => Auth::id()]);
                $approvers = [];
            }

            return view('hrm::requisitions.create', compact('approvers'));
        } catch (\Exception $e) {
            // Log any errors
            \Log::error('Error in RequisitionController::create', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            // Return an empty approvers array
            $approvers = [];
            return view('hrm::requisitions.create', compact('approvers'))
                ->withErrors(__('An error occurred while loading the requisition form: ' . $e->getMessage()));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'first_approver_id' => 'required|exists:users,id',
        ]);

        $gcoo = User::whereHas('roles', function ($query) {
            $query->where('name', 'gcoo');
        })->first();

        $accountant = User::whereHas('roles', function ($query) {
            $query->where('name', 'accountant');
        })->first();

        if (!$gcoo || !$accountant) {
            return redirect()->back()->withErrors(__('GCOO or Accountant role not assigned.'));
        }

        Requisition::create([
            'employee_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'first_approver_id' => $request->first_approver_id,
            'first_approver_status' => 'pending',
            'gcoo_id' => 8, // Use dynamic ID
            'gcoo_status' => 'pending',
            'accountant_id' => 695, // Use dynamic ID
            'accountant_status' => 'pending',
            'status' => 'pending',
        ]);

        return redirect()->route('requisitions.index')->with('success', __('Requisition submitted successfully.'));
    }

    public function edit(Requisition $requisition)
    {
        if ($requisition->status !== 'pending' || $requisition->employee_id !== Auth::id()) {
            return redirect()->back()->withErrors(__('Unauthorized or requisition not editable.'));
        }

        $approvers = User::where('id', '!=', Auth::id())
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['gcoo', 'accountant']);
            })
            ->pluck('name', 'id'); // Pluck for dropdown consistency

        return view('hrm::requisitions.edit', compact('requisition', 'approvers'));
    }

    public function update(Request $request, Requisition $requisition)
    {
        if ($requisition->status !== 'pending' || $requisition->employee_id !== Auth::id()) {
            return redirect()->back()->withErrors(__('Unauthorized or requisition not editable.'));
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'first_approver_id' => 'required|exists:users,id',
        ]);

        $requisition->update([
            'title' => $request->title,
            'description' => $request->description,
            'first_approver_id' => $request->first_approver_id,
        ]);

        return redirect()->route('requisitions.index')->with('success', __('Requisition updated successfully.'));
    }

    public function destroy(Requisition $requisition)
    {
        if ($requisition->status !== 'pending' || $requisition->employee_id !== Auth::id()) {
            return redirect()->back()->withErrors(__('Unauthorized or requisition not deletable.'));
        }

        $requisition->delete();

        return redirect()->route('requisitions.index')->with('success', __('Requisition deleted successfully.'));
    }

    public function approveForm(Requisition $requisition)
    {
        $user = Auth::user();
        if (
            ($requisition->first_approver_status === 'pending' && $requisition->first_approver_id === $user->id) ||
            ($requisition->gcoo_status === 'pending' && $requisition->gcoo_id === $user->id && $requisition->first_approver_status === 'approved') ||
            ($requisition->accountant_status === 'pending' && $requisition->accountant_id === $user->id && $requisition->gcoo_status === 'approved')
        ) {
            return view('hrm::requisitions.action', compact('requisition'));
        }

        return redirect()->back()->withErrors(__('Unauthorized action.'));
    }

    public function rejectForm(Requisition $requisition)
    {
        return $this->approveForm($requisition); // Same form for approve/reject
    }

    public function approve(Request $request, Requisition $requisition)
    {
        $user = Auth::user();
        $request->validate([
            'action' => 'required|in:approve,reject',
            'remark' => 'required|string',
        ]);

        $updateData = [
            'status' => $requisition->overall_status, // Update overall status via accessor
        ];

        if ($requisition->first_approver_status === 'pending' && $user->id === $requisition->first_approver_id) {
            $updateData['first_approver_status'] = $request->action === 'approve' ? 'approved' : 'rejected';
            $updateData['first_approver_remark'] = $request->remark;
        } elseif ($requisition->gcoo_status === 'pending' && $user->id === $requisition->gcoo_id && $requisition->first_approver_status === 'approved') {
            $updateData['gcoo_status'] = $request->action === 'approve' ? 'approved' : 'rejected';
            $updateData['gcoo_remark'] = $request->remark;
        } elseif ($requisition->accountant_status === 'pending' && $user->id === $requisition->accountant_id && $requisition->gcoo_status === 'approved') {
            $updateData['accountant_status'] = $request->action === 'approve' ? 'approved' : 'rejected';
            $updateData['accountant_remark'] = $request->remark;
        } else {
            return redirect()->back()->withErrors(__('Unauthorized or invalid action.'));
        }

        $requisition->update($updateData);

        return redirect()->route('requisitions.index')->with('success', __('Requisition updated successfully.'));
    }
}