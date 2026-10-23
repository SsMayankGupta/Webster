<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\RebateLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
}
