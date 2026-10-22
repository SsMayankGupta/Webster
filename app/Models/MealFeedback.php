<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealFeedback extends Model
{
    protected $fillable = [
        'student_id', 'menu_id', 'rating', 'hygiene_rating', 'comment',
        'feedback_type', 'contractor_action', 'contractor_note',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'hygiene_rating' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
