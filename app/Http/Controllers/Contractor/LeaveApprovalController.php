<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\RebateLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $leaves = LeaveRequest::with('student')->latest()->get();
        return view('contractor.leaves.index', compact('leaves'));
    }

    public function approve(LeaveRequest $leave)
    {
        $contractor = Auth::guard('contractor')->user();
        DB::transaction(function () use ($leave, $contractor) {
            $leave->lockForUpdate();
            if ($leave->status !== 'pending') {
                return;
            }
            $days = $leave->from_date->diffInDays($leave->to_date) + 1;
            $amount = $days * 50;
            $leave->update(['status' => 'approved', 'approved_by' => $contractor->id]);
            RebateLedger::create([
                'student_id' => $leave->student_id,
                'leave_request_id' => $leave->id,
                'amount' => $amount,
                'reason' => 'Leave rebate',
                'created_at' => now(),
            ]);
        });
        return back()->with('status', 'Leave approved.');
    }

    public function reject(LeaveRequest $leave)
    {
        $leave->update(['status' => 'rejected']);
        return back()->with('status', 'Leave rejected.');
    }
}
