<?php

namespace App\Livewire;

use App\Models\LeaveRequest;
use App\Models\MealRefund;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\ExtraMealBillingService;
use App\Services\RebateService;
use Illuminate\Support\Carbon;
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

    public ?int $selectedStudentId = null;

    public ?int $selectedLeaveId = null;

    public string $feeAmount = '';

    public string $refundAmount = '';

    public string $extraMealAmount = '';

    public string $semesterName = '';

    public string $semesterStart = '';

    public string $semesterEnd = '';

    public function mount(): void
    {
        $contractor = $this->contractor();
        $this->semesterName = $contractor->semester_name ?? '';
        $this->semesterStart = $contractor->semester_starts_at?->toDateString() ?? '';
        $this->semesterEnd = $contractor->semester_ends_at?->toDateString() ?? '';
        $this->loadLeaves();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadLeaves();
    }

    public function loadLeaves(): void
    {
        $contractor = $this->contractor();
        $query = LeaveRequest::query()
            ->whereHas('student', fn ($query) => $query->where('contractor_id', $contractor->id))
            ->with(['student:id,name,college_id,contractor_id', 'rebates']);

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $leaves = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        $this->leaves = $leaves->map(fn (LeaveRequest $leave): array => [
            'id' => $leave->id,
            'student_name' => $leave->student->name,
            'student_college_id' => $leave->student->college_id,
            'from_date' => $leave->from_date->format('d M Y'),
            'to_date' => $leave->to_date->format('d M Y'),
            'days' => $leave->from_date->diffInDays($leave->to_date) + 1,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'rebate_amount' => $leave->rebates->sum(
                fn ($rebate): int => (int) ($rebate->amount_paise ?? round(((float) $rebate->amount) * 100))
            ) / 100,
        ])->values()->all();
    }

    public function approveLeave(int $leaveId): void
    {
        $contractor = $this->contractor();
        $leave = $this->contractorLeave($contractor, $leaveId);
        app(RebateService::class)->approve($leave, $contractor);

        $this->loadLeaves();
        $this->dispatch('portal-toast', message: 'Leave approved and rebate credited.', type: 'success');
    }

    public function rejectLeave(int $leaveId): void
    {
        $leave = $this->contractorLeave($this->contractor(), $leaveId);
        app(RebateService::class)->reject($leave);

        $this->loadLeaves();
        $this->dispatch('portal-toast', message: 'Leave rejected.', type: 'warning');
    }

    public function addFeePayment(): void
    {
        $contractor = $this->contractor();
        $validated = $this->validate([
            'feeAmount' => ['required', 'numeric', 'gt:0', 'max:10000000', 'regex:/^\d+(?:\.\d{1,2})?$/'],
        ]);
        $student = $this->selectedStudent($contractor);

        StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => $this->amountToPaise($validated['feeAmount']),
        ]);

        $this->feeAmount = '';
        $this->dispatch('portal-toast', message: 'Fee payment recorded.', type: 'success');
    }

    public function addMealRefund(): void
    {
        $contractor = $this->contractor();
        $validated = $this->validate([
            'refundAmount' => ['required', 'numeric', 'gt:0', 'max:10000000', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'selectedLeaveId' => ['required', 'integer'],
        ]);
        $student = $this->selectedStudent($contractor);
        $leave = $student->leaveRequests()
            ->where('status', 'approved')
            ->findOrFail($validated['selectedLeaveId']);

        MealRefund::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $leave->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => $this->amountToPaise($validated['refundAmount']),
            'status' => 'pending',
        ]);

        $this->refundAmount = '';
        $this->dispatch('portal-toast', message: 'Meal refund recorded and awaiting transfer.', type: 'success');
    }

    public function addExtraMealCollection(): void
    {
        $contractor = $this->contractor();
        $validated = $this->validate([
            'extraMealAmount' => ['required', 'numeric', 'gt:0', 'max:10000000', 'regex:/^\d+(?:\.\d{1,2})?$/'],
        ]);
        $student = $this->selectedStudent($contractor);

        app(ExtraMealBillingService::class)->recordCollection(
            $student,
            $contractor,
            $this->amountToPaise($validated['extraMealAmount']),
        );

        $this->extraMealAmount = '';
        $this->dispatch('portal-toast', message: 'Extra-meal payment collection recorded.', type: 'success');
    }

    public function markRefundTransferred(int $refundId): void
    {
        $contractor = $this->contractor();
        $refund = MealRefund::query()
            ->whereKey($refundId)
            ->whereHas('student', fn ($query) => $query->where('contractor_id', $contractor->id))
            ->firstOrFail();

        if ($refund->status !== 'transferred') {
            $refund->forceFill([
                'status' => 'transferred',
                'transferred_at' => now(),
                'transferred_by' => $contractor->id,
            ])->save();
        }

        $this->dispatch('portal-toast', message: 'Refund marked as transferred.', type: 'success');
    }

    public function saveSemesterPeriod(): void
    {
        $contractor = $this->contractor();
        $validated = $this->validate([
            'semesterName' => ['required', 'string', 'max:100'],
            'semesterStart' => ['required', 'date_format:Y-m-d'],
            'semesterEnd' => ['required', 'date_format:Y-m-d', 'after_or_equal:semesterStart'],
        ]);

        $contractor->update([
            'semester_name' => $validated['semesterName'],
            'semester_starts_at' => $validated['semesterStart'],
            'semester_ends_at' => $validated['semesterEnd'],
        ]);

        $this->dispatch('portal-toast', message: 'Semester dates saved. Wallet totals now use this date range.', type: 'success');
    }

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'pending', 'approved', 'rejected'], true)) {
            return;
        }

        $this->filter = $filter;
        $this->loadLeaves();
    }

    public function updatedSelectedStudentId(): void
    {
        $this->selectedLeaveId = null;
        $this->extraMealAmount = '';
        $this->resetValidation(['selectedLeaveId']);
    }

    public function render()
    {
        $contractor = $this->contractor();
        $students = $contractor->students()
            ->select(['id', 'name', 'college_id', 'contractor_id', 'archived_at'])
            ->orderBy('name')
            ->orderBy('id')
            ->get();
        $walletStudent = $this->selectedStudentId === null
            ? null
            : $contractor->students()
                ->with([
                    'feePayments' => fn ($query) => $query->orderByDesc('created_at')->orderByDesc('id')->limit(10),
                    'mealRefunds' => fn ($query) => $query
                        ->with('leaveRequest:id,from_date,to_date')
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit(10),
                    'extraMealCharges' => fn ($query) => $query
                        ->with('booking.menu:id,meal_type,serve_at')
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit(10),
                    'extraMealCollections' => fn ($query) => $query
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->limit(10),
                ])
                ->find($this->selectedStudentId);
        $semesterRange = $this->semesterRange($contractor);
        $feeQuery = $walletStudent?->feePayments();
        $refundQuery = $walletStudent?->mealRefunds();
        $rebateQuery = $walletStudent?->rebateLedgers()->whereNotNull('leave_request_id');
        $extraMealSummary = $walletStudent === null
            ? ['charged_paise' => 0, 'collected_paise' => 0, 'outstanding_paise' => 0]
            : app(ExtraMealBillingService::class)->summary($walletStudent);

        if ($semesterRange !== null) {
            $feeQuery?->whereBetween('created_at', $semesterRange);
            $refundQuery?->whereBetween('created_at', $semesterRange);
            $rebateQuery?->whereBetween('created_at', $semesterRange);
        }

        return view('livewire.contractor-leave', [
            'students' => $students,
            'walletStudent' => $walletStudent,
            'approvedLeaves' => $walletStudent?->leaveRequests()
                ->where('status', 'approved')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(['id', 'from_date', 'to_date']) ?? collect(),
            'feeTotalPaise' => $feeQuery?->sum('amount_paise') ?? 0,
            'refundTotalPaise' => $refundQuery?->sum('amount_paise') ?? 0,
            'transferredRefundPaise' => (int) $walletStudent?->mealRefunds()->where('status', 'transferred')
                ->when($semesterRange !== null, fn ($query) => $query->whereBetween('created_at', $semesterRange))
                ->sum('amount_paise'),
            'leaveRebatePaise' => $rebateQuery?->get(['amount_paise', 'amount'])->sum(
                fn ($rebate): int => (int) ($rebate->amount_paise ?? round(((float) $rebate->amount) * 100))
            ),
            'extraMealChargedPaise' => $extraMealSummary['charged_paise'],
            'extraMealCollectedPaise' => $extraMealSummary['collected_paise'],
            'extraMealOutstandingPaise' => $extraMealSummary['outstanding_paise'],
            'semesterEnded' => $contractor->semester_ends_at !== null
                && $contractor->semester_ends_at->endOfDay()->isPast(),
            'semesterRange' => $semesterRange,
            'semesterName' => $this->semesterName,
        ]);
    }

    private function contractor(): User
    {
        $contractor = Auth::guard('contractor')->user();
        abort_unless($contractor instanceof User && $contractor->role === 'contractor', 403);

        return $contractor;
    }

    private function contractorLeave(User $contractor, int $leaveId): LeaveRequest
    {
        return LeaveRequest::query()
            ->whereKey($leaveId)
            ->whereHas('student', fn ($query) => $query->where('contractor_id', $contractor->id))
            ->firstOrFail();
    }

    private function selectedStudent(User $contractor): User
    {
        abort_if($this->selectedStudentId === null, 404);

        return $contractor->students()
            ->findOrFail($this->selectedStudentId);
    }

    private function amountToPaise(string $amount): int
    {
        [$rupees, $paise] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $rupees * 100) + (int) str_pad($paise, 2, '0');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function semesterRange(User $contractor): ?array
    {
        if ($contractor->semester_starts_at === null || $contractor->semester_ends_at === null) {
            return null;
        }

        return [
            $contractor->semester_starts_at->copy()->startOfDay(),
            $contractor->semester_ends_at->copy()->endOfDay(),
        ];
    }
}
