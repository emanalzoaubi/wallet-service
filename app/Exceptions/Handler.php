<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler
{
    /**
     * Handle exceptions and return consistent JSON responses.
     */
    public function __invoke(Request $request, Throwable $exception): ?JsonResponse
    {
        // Handle ModelNotFoundException (404)
        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFound($exception);
        }

        // Handle NotFoundHttpException (404)
        if ($exception instanceof NotFoundHttpException) {
            return $this->handleNotFound($exception);
        }

        // Handle ValidationException (422)
        if ($exception instanceof ValidationException) {
            return $this->handleValidation($exception);
        }

        // Handle custom application exceptions
        if ($exception instanceof InsufficientFundsException) {
            return $this->handleInsufficientFunds($exception);
        }

        if ($exception instanceof CurrencyMismatchException) {
            return $this->handleCurrencyMismatch($exception);
        }

        if ($exception instanceof IdempotencyKeyViolationException) {
            return $this->handleIdempotencyViolation($exception);
        }

        if ($exception instanceof InvalidTransferException) {
            return $this->handleInvalidTransfer($exception);
        }

        // Handle all other exceptions
        return $this->handleGenericException($exception);
    }

    /**
     * Handle ModelNotFoundException.
     */
    private function handleModelNotFound(ModelNotFoundException $exception): JsonResponse
    {
        $model = class_basename($exception->getModel());
        $message = "{$model} not found.";

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Handle NotFoundHttpException.
     */
    private function handleNotFound(NotFoundHttpException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Resource not found.',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Handle ValidationException.
     */
    private function handleValidation(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $exception->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle InsufficientFundsException.
     */
    private function handleInsufficientFunds(InsufficientFundsException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage() ?: 'Your wallet balance is too low for this transaction.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle CurrencyMismatchException.
     */
    private function handleCurrencyMismatch(CurrencyMismatchException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage() ?: 'Transfer must use the same currency for both wallets.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle IdempotencyKeyViolationException.
     */
    private function handleIdempotencyViolation(IdempotencyKeyViolationException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage() ?: 'This idempotency key has already been used with different transaction parameters.',
        ], Response::HTTP_CONFLICT);
    }

    /**
     * Handle InvalidTransferException.
     */
    private function handleInvalidTransfer(InvalidTransferException $exception): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $exception->getMessage() ?: 'The transfer operation is invalid.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle generic exceptions.
     */
    private function handleGenericException(Throwable $exception): JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        // Check if exception implements HttpExceptionInterface
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        $message = config('app.debug')
            ? $exception->getMessage()
            : 'An error occurred while processing your request.';

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}

