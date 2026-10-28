<?php

namespace App\Livewire;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Ticket Management')]
class ContractorTicket extends Component
{
    private const STATUS_TRANSITIONS = [
        'pending' => 'under_review',
        'under_review' => 'resolved',
    ];

    private const TICKET_STATUSES = ['pending', 'under_review', 'resolved'];

    public array $tickets = [];

    public string $filter = 'all';

    public function mount(): void
    {
        $this->loadTickets();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadTickets();
    }

    public function loadTickets(): void
    {
        $query = $this->ticketsQuery();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $tickets = $query->latest()->get();

        $this->tickets = $tickets->map(fn (Ticket $ticket): array => [
            'id' => $ticket->id,
            'student_name' => $ticket->student->name,
            'student_college_id' => $ticket->student->college_id,
            'subject' => Str::headline($ticket->category),
            'description' => $ticket->text,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at->format('d M Y · g:i A'),
        ])->values()->all();
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $ticket = $this->ticketsQuery()->findOrFail($ticketId);
        $nextStatus = self::STATUS_TRANSITIONS[$ticket->status] ?? null;

        if ($status !== $nextStatus) {
            throw ValidationException::withMessages([
                'status' => 'This ticket status transition is not allowed.',
            ]);
        }

        $ticket->update(['status' => $status]);

        $this->loadTickets();
        $this->dispatch('portal-toast', message: 'Ticket status updated.', type: 'success');
    }

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', ...self::TICKET_STATUSES], true)) {
            throw ValidationException::withMessages([
                'filter' => 'The selected ticket filter is invalid.',
            ]);
        }

        $this->filter = $filter;
        $this->loadTickets();
    }

    private function ticketsQuery(): Builder
    {
        return Ticket::query()
            ->whereHas('student', fn (Builder $query) => $query
                ->where('contractor_id', Auth::guard('contractor')->id())
                ->where('role', 'student'))
            ->with('student');
    }

    public function render()
    {
        return view('livewire.contractor-ticket');
    }
}
