<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $leaves = LeaveRequest::where('student_id', $student->id)->latest()->get();

        return view('student.leave.index', compact('leaves'));
    }

    public function create()
    {
        return view('student.leave.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $student = Auth::guard('student')->user();

        LeaveRequest::create([
            'student_id' => $student->id,
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('student.leave.index')->with('status', 'Leave requested.');
    }

    public function show(LeaveRequest $leave)
    {
        $this->authorizeOwner($leave);

        return view('student.leave.show', compact('leave'));
    }

    public function edit(LeaveRequest $leave)
    {
        $this->authorizeOwner($leave);

        return view('student.leave.edit', compact('leave'));
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $this->authorizeOwner($leave);

        $data = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($leave->status !== 'pending') {
            return back()->withErrors(['leave' => 'Only pending leave requests can be edited.']);
        }

        $leave->update($data);

        return redirect()->route('student.leave.index')->with('status', 'Leave request updated.');
    }

    public function destroy(LeaveRequest $leave)
    {
        $this->authorizeOwner($leave);

        if ($leave->status === 'approved') {
            return back()->withErrors(['leave' => 'Approved leave cannot be deleted.']);
        }

        $leave->delete();

        return back()->with('status', 'Leave request removed.');
    }

    private function authorizeOwner(LeaveRequest $leave): void
    {
        abort_unless($leave->student_id === Auth::guard('student')->id(), 403);
    }
}
