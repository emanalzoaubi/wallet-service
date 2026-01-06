<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class InsufficientFundsException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'Insufficient Funds',
            'message' => $this->getMessage() ?: 'Your wallet balance is too low for this transaction.',
        ], 422);
    }
}