<?php

namespace App\Livewire;

use App\Models\LeaveRequest;
use App\Services\RebateService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Leave Approvals')]
class ContractorLeave extends Component
{
    public array $leaves = [];

    public string $filter = 'all';

    public function mount(): void
    {
        $this->loadLeaves();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadLeaves();
    }

    public function loadLeaves(): void
    {
        $query = LeaveRequest::query()->with(['student', 'rebates']);

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $leaves = $query->latest()->get();

        $this->leaves = $leaves->map(fn (LeaveRequest $leave): array => [
            'id' => $leave->id,
            'student_name' => $leave->student->name,
            'student_college_id' => $leave->student->college_id,
            'from_date' => $leave->from_date->format('d M Y'),
            'to_date' => $leave->to_date->format('d M Y'),
            'days' => $leave->from_date->diffInDays($leave->to_date) + 1,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'rebate_amount' => $leave->rebates->sum('amount_paise') / 100,
        ])->values()->all();
    }

    public function approveLeave(int $leaveId): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $leave = LeaveRequest::query()->findOrFail($leaveId);
        app(RebateService::class)->approve($leave, $contractor);

        $this->loadLeaves();
        $this->dispatch('portal-toast', message: 'Leave approved and rebate credited.', type: 'success');
    }

    public function rejectLeave(int $leaveId): void
    {
        $leave = LeaveRequest::query()->findOrFail($leaveId);
        app(RebateService::class)->reject($leave);

        $this->loadLeaves();
        $this->dispatch('portal-toast', message: 'Leave rejected.', type: 'warning');
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->loadLeaves();
    }

    public function render()
    {
        return view('livewire.contractor-leave');
    }
}
