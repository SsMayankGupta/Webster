<?php

namespace App\Services;

use App\Models\RebateLedger;
use App\Models\User;
use Carbon\CarbonImmutable;

class BillingService
{
    /**
     * Build the monthly statement in integer paise.
     *
     * @return array<string, mixed>
     */
    public function statement(User $student, ?string $month = null): array
    {
        $start = $this->monthStart($month);
        $end = $start->endOfMonth();

        $rebates = RebateLedger::query()
            ->where('student_id', $student->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $rebatePaise = (int) $rebates->sum(
            fn (RebateLedger $rebate): int => $this->paiseFor($rebate)
        );

        $basePaise = (int) config('mess.monthly_base_paise');

        return [
            'month' => $start->format('Y-m'),
            'label' => $start->format('F Y'),
            'base_paise' => $basePaise,
            'rebate_paise' => $rebatePaise,
            'net_paise' => $basePaise - $rebatePaise,
            'entries' => $rebates->map(fn (RebateLedger $rebate): array => [
                'id' => $rebate->id,
                'date' => $rebate->created_at->toDateString(),
                'reason' => $rebate->reason,
                'amount_paise' => $this->paiseFor($rebate),
            ])->values()->all(),
        ];
    }

    /**
     * @return list<array{month: string, label: string}>
     */
    public function monthOptions(int $count = 6): array
    {
        $options = [];
        $cursor = CarbonImmutable::now()->startOfMonth();

        for ($i = 0; $i < $count; $i++) {
            $options[] = [
                'month' => $cursor->format('Y-m'),
                'label' => $cursor->format('F Y'),
            ];
            $cursor = $cursor->subMonth();
        }

        return $options;
    }

    public function monthStart(?string $month): CarbonImmutable
    {
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $parsed = CarbonImmutable::createFromFormat('!Y-m', $month);

            if ($parsed !== false) {
                return $parsed->startOfMonth();
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    public function paiseFor(RebateLedger $rebate): int
    {
        if ($rebate->amount_paise !== null) {
            return (int) $rebate->amount_paise;
        }

        return (int) round(((float) $rebate->amount) * 100);
    }
}
