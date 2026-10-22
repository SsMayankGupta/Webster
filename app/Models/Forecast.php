<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Forecast extends Model
{
    protected $fillable = [
        'menu_id', 'ewma_show_rate', 'forecasted_quantity', 'actual_consumption', 'forecast_accuracy',
    ];

    protected function casts(): array
    {
        return [
            'forecast_accuracy' => 'decimal:2',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
