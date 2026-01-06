<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class CurrencyMismatchException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => 'Currency Mismatch',
            'message' => $this->getMessage() ?: 'Transfer must use the same currency for both wallets.',
        ], 422);
    }
}

