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
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
        $student = Auth::guard('student')->user();
        LeaveRequest::create([
            'student_id' => $student->id,
            'from_date' => $data['from_date'],
            'to_date' => $data['to_date'],
            'status' => 'pending',
        ]);
        return redirect()->route('student.leave.index')->with('status', 'Leave requested.');
    }

    public function show(LeaveRequest $leave) { return view('student.leave.show', compact('leave')); }
    public function edit(LeaveRequest $leave) { return view('student.leave.edit', compact('leave')); }
    public function update(Request $r, LeaveRequest $leave) { return back(); }
    public function destroy(LeaveRequest $leave) { $leave->delete(); return back(); }
}
