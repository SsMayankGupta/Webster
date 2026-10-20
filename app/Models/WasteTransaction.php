<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteTransaction extends Model
{
    protected $fillable = ['contractor_id', 'weight_kg', 'type', 'earnings', 'logged_at'];

    protected function casts(): array
    {
        return ['weight_kg' => 'decimal:2', 'earnings' => 'decimal:2', 'logged_at' => 'datetime'];
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }
}
