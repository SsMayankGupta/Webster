<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveEndDateModification;
use App\Models\MealBooking;
use App\Models\RebateLedger;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RebateService
{
    public function __construct(private readonly ApprovedLeaveService $approvedLeaves) {}

    /**
     * Approve a leave request and post an append-only rebate in integer paise.
     */
    public function approve(LeaveRequest $leave, User $approver): LeaveRequest
    {
        return DB::transaction(function () use ($leave, $approver): LeaveRequest {
            $locked = LeaveRequest::query()->whereKey($leave->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'approved') {
                return $locked;
            }

            User::query()->whereKey($locked->student_id)->lockForUpdate()->firstOrFail();
            $approvedRanges = $this->approvedLeaves->rangesBetween(
                (int) $locked->student_id,
                $locked->from_date,
                $locked->to_date,
            );
            $uncreditedDays = $this->approvedLeaves->uncreditedDays($locked, $approvedRanges);
            $paise = $uncreditedDays * (int) config('mess.rebate_per_day_paise');

            $locked->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
            ]);

            RebateLedger::query()->firstOrCreate(
                ['leave_request_id' => $locked->id],
                [
                    'student_id' => $locked->student_id,
                    'amount' => round($paise / 100, 2),
                    'amount_paise' => $paise,
                    'reason' => sprintf(
                        'Leave rebate (%d eligible day%s)',
                        $uncreditedDays,
                        $uncreditedDays === 1 ? '' : 's',
                    ),
                    'created_at' => now(),
                ],
            );

            MealBooking::query()
                ->where('student_id', $locked->student_id)
                ->where('status', 'booked')
                ->whereHas('menu', function ($query) use ($locked): void {
                    $query->whereBetween('serve_at', [
                        $locked->from_date->copy()->startOfDay(),
                        $locked->to_date->copy()->endOfDay(),
                    ]);
                })
                ->update([
                    'status' => 'cancelled',
                    'confirmed_at' => now(),
                ]);

            return $locked->fresh(['rebates']);
        });
    }

    public function reject(LeaveRequest $leave): LeaveRequest
    {
        $leave->update(['status' => 'rejected']);

        return $leave;
    }

    public function updateLeaveEndDate(LeaveRequest $leave, User $contractor, string $newEndDate): LeaveRequest
    {
        return DB::transaction(function () use ($leave, $contractor, $newEndDate): LeaveRequest {
            $locked = LeaveRequest::query()->whereKey($leave->id)->lockForUpdate()->firstOrFail();
            $student = User::query()->whereKey($locked->student_id)->lockForUpdate()->firstOrFail();

            abort_unless(
                $contractor->role === 'contractor'
                    && (int) $student->contractor_id === (int) $contractor->id,
                404,
            );

            if ($locked->status !== 'approved') {
                throw ValidationException::withMessages([
                    'returnDates' => 'Only approved leave end dates can be modified.',
                ]);
            }

            $endDate = CarbonImmutable::parse($newEndDate)->startOfDay();
            $originalEndDate = CarbonImmutable::instance($locked->requested_to_date ?? $locked->to_date)->startOfDay();
            $start = CarbonImmutable::instance($locked->from_date)->startOfDay();

            if ($endDate->lt($start) || $endDate->gt($originalEndDate)) {
                throw ValidationException::withMessages([
                    'returnDates' => 'The leave end date must be between the leave start date and its originally approved end date.',
                ]);
            }

            $previousEndDate = CarbonImmutable::instance($locked->to_date)->startOfDay();
            if ($endDate->equalTo($previousEndDate)) {
                return $locked->fresh(['rebates', 'endDateModifications']);
            }

            $locked->update([
                'requested_to_date' => $locked->requested_to_date ?? $locked->to_date,
                'to_date' => $endDate,
            ]);

            $approvedLeaves = LeaveRequest::query()
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->orderBy('from_date')
                ->orderBy('id')
                ->get(['from_date', 'to_date']);
            $eligibleDays = 0;
            $lastCoveredDate = null;

            foreach ($approvedLeaves as $approvedLeave) {
                $rangeStart = CarbonImmutable::instance($approvedLeave->from_date)->startOfDay();
                $rangeEnd = CarbonImmutable::instance($approvedLeave->to_date)->startOfDay();

                if ($lastCoveredDate === null || $rangeStart->gt($lastCoveredDate->addDay())) {
                    $eligibleDays += (int) $rangeStart->diffInDays($rangeEnd) + 1;
                    $lastCoveredDate = $rangeEnd;

                    continue;
                }

                if ($rangeEnd->gt($lastCoveredDate)) {
                    $eligibleDays += (int) $lastCoveredDate->diffInDays($rangeEnd);
                    $lastCoveredDate = $rangeEnd;
                }
            }

            $existingPaise = RebateLedger::query()
                ->where('student_id', $student->id)
                ->get(['amount_paise', 'amount'])
                ->sum(fn (RebateLedger $rebate): int => (int) ($rebate->amount_paise
                    ?? round(((float) $rebate->amount) * 100)));
            $recalculatedPaise = $eligibleDays * (int) config('mess.rebate_per_day_paise');
            $adjustmentPaise = $recalculatedPaise - $existingPaise;

            if ($adjustmentPaise !== 0) {
                RebateLedger::query()->create([
                    'student_id' => $student->id,
                    'leave_request_id' => $locked->id,
                    'amount' => round($adjustmentPaise / 100, 2),
                    'amount_paise' => $adjustmentPaise,
                    'reason' => sprintf(
                        'Rebate adjustment after leave end-date change (%d eligible day%s)',
                        $eligibleDays,
                        $eligibleDays === 1 ? '' : 's',
                    ),
                    'created_at' => now(),
                ]);
            }

            LeaveEndDateModification::query()->create([
                'leave_request_id' => $locked->id,
                'modified_by' => $contractor->id,
                'modified_by_name' => $contractor->name,
                'previous_to_date' => $previousEndDate,
                'new_to_date' => $endDate,
                'rebate_adjustment_paise' => $adjustmentPaise,
                'created_at' => now(),
            ]);

            return $locked->fresh(['rebates', 'endDateModifications']);
        });
    }
}
