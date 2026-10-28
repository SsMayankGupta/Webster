<?php

namespace App\Services;

use App\Models\ExtraMealCollection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExtraMealBillingService
{
    /**
     * @return array{charged_paise: int, collected_paise: int, outstanding_paise: int}
     */
    public function summary(User $student): array
    {
        $charged = (int) $student->extraMealCharges()->sum('amount_paise');
        $collected = (int) $student->extraMealCollections()->sum('amount_paise');

        return [
            'charged_paise' => $charged,
            'collected_paise' => $collected,
            'outstanding_paise' => max(0, $charged - $collected),
        ];
    }

    public function recordCollection(User $student, User $contractor, int $amountPaise): ExtraMealCollection
    {
        abort_unless($contractor->role === 'contractor', 403);
        abort_unless(
            $contractor->students()->whereKey($student->id)->exists(),
            404,
        );

        return DB::transaction(function () use ($student, $contractor, $amountPaise): ExtraMealCollection {
            User::query()->whereKey($student->id)->lockForUpdate()->firstOrFail();

            $summary = $this->summary($student->fresh());
            if ($amountPaise > $summary['outstanding_paise']) {
                throw ValidationException::withMessages([
                    'extraMealAmount' => 'The collection cannot exceed the student’s outstanding extra-meal balance.',
                ]);
            }

            return ExtraMealCollection::query()->create([
                'student_id' => $student->id,
                'recorded_by' => $contractor->id,
                'amount_paise' => $amountPaise,
            ]);
        });
    }
}
