<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Ticket Management')]
class ContractorTicket extends Component
{
    public array $tickets = [];

    public string $filter = 'all';

    public function mount(): void
    {
        $this->loadTickets();
    }

    public function loadTickets(): void
    {
        $query = Ticket::query()->with('student');

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $tickets = $query->latest()->get();

        $this->tickets = $tickets->map(fn (Ticket $ticket): array => [
            'id' => $ticket->id,
            'student_name' => $ticket->student->name,
            'student_college_id' => $ticket->student->college_id,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at->format('d M Y · g:i A'),
        ])->values()->all();
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $ticket = Ticket::query()->findOrFail($ticketId);
        $ticket->update(['status' => $status]);

        $this->loadTickets();
        $this->dispatch('portal-toast', message: 'Ticket status updated.', type: 'success');
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->loadTickets();
    }

    public function render()
    {
        return view('livewire.contractor-ticket');
    }
}
