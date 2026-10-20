<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $tickets = Ticket::where('student_id', $student->id)->latest()->get();
        return view('student.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('student.tickets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'text' => 'required|string',
        ]);
        $student = Auth::guard('student')->user();
        Ticket::create([
            'student_id' => $student->id,
            'category' => $data['category'],
            'text' => $data['text'],
            'status' => 'pending',
        ]);
        return redirect()->route('student.tickets.index')->with('status', 'Ticket raised.');
    }

    public function show(Ticket $ticket) { return view('student.tickets.show', compact('ticket')); }
    public function edit(Ticket $ticket) { return view('student.tickets.edit', compact('ticket')); }
    public function update(Request $r, Ticket $ticket) { return back(); }
    public function destroy(Ticket $ticket) { $ticket->delete(); return back(); }
}
