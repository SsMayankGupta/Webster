<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

class ApprovedLeaveService
{
    /**
     * @return Collection<int, LeaveRequest>
     */
    public function rangesBetween(int $studentId, DateTimeInterface $from, DateTimeInterface $to): Collection
    {
        return LeaveRequest::query()
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', CarbonImmutable::instance($to)->toDateString())
            ->whereDate('to_date', '>=', CarbonImmutable::instance($from)->toDateString())
            ->get(['from_date', 'to_date']);
    }

    public function coversDate(int $studentId, DateTimeInterface $date): bool
    {
        $dateString = CarbonImmutable::instance($date)->toDateString();

        return LeaveRequest::query()
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $dateString)
            ->whereDate('to_date', '>=', $dateString)
            ->exists();
    }

    /**
     * @param  Collection<int, LeaveRequest>  $ranges
     */
    public function containsDate(Collection $ranges, DateTimeInterface $date): bool
    {
        $dateString = CarbonImmutable::instance($date)->toDateString();

        return $ranges->contains(
            fn (LeaveRequest $leave): bool => $leave->from_date->toDateString() <= $dateString
                && $leave->to_date->toDateString() >= $dateString,
        );
    }

    /**
     * Count leave days that have not already been credited by another approved request.
     *
     * @param  Collection<int, LeaveRequest>  $approvedRanges
     */
    public function uncreditedDays(LeaveRequest $leave, Collection $approvedRanges): int
    {
        $days = 0;
        $date = CarbonImmutable::instance($leave->from_date)->startOfDay();
        $end = CarbonImmutable::instance($leave->to_date)->startOfDay();

        while ($date->lte($end)) {
            if (! $this->containsDate($approvedRanges, $date)) {
                $days++;
            }

            $date = $date->addDay();
        }

        return $days;
    }
}
