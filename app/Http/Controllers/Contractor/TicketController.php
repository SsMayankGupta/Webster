<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with('student')->latest()->get();
        return view('contractor.tickets.index', compact('tickets'));
    }

    public function updateStatus(\Illuminate\Http\Request $request, Ticket $ticket)
    {
        $data = $request->validate(['status' => 'required|in:pending,under_review,resolved']);
        $ticket->update(['status' => $data['status']]);
        return back()->with('status', 'Ticket updated.');
    }
}
