<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\RebateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveApprovalController extends Controller
{
    public function __construct(private readonly RebateService $rebates) {}

    public function index()
    {
        $leaves = LeaveRequest::with(['student', 'rebates'])->latest()->get();

        return view('contractor.leaves.index', compact('leaves'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $contractor = Auth::guard('contractor')->user() ?? $request->user();

        $this->rebates->approve($leave, $contractor);

        return back()->with('status', 'Leave approved and rebate credited.');
    }

    public function reject(LeaveRequest $leave)
    {
        $this->rebates->reject($leave);

        return back()->with('status', 'Leave rejected.');
    }
}
