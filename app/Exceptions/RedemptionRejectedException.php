<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class RedemptionRejectedException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 409)
    {
        parent::__construct($message);
    }

    public function render(Request $request): Response
    {
        return response()->json(['ok' => false, 'message' => $this->getMessage()], $this->status);
    }
}
