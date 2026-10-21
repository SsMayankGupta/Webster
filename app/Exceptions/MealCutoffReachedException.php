<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class MealCutoffReachedException extends RuntimeException
{
    public function __construct(string $message = 'The 4-hour confirmation cutoff for this meal has passed.')
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson() && ! $request->hasHeader('X-Livewire')) {
            return response()->json(['message' => $this->getMessage()], 409);
        }

        return back()->withErrors(['meal' => $this->getMessage()]);
    }
}
