<?php

namespace App\Exceptions;

use App\Services\ApiResponseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Handle unauthenticated users.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return ApiResponseService::unauthorized('Authentication required');
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle unauthorized HTTP exceptions.
     */
    protected function handleUnauthorizedHttpException(UnauthorizedHttpException $e, Request $request): JsonResponse
    {
        if ($request->expectsJson()) {
            return ApiResponseService::unauthorized('Access denied');
        }

        return ApiResponseService::unauthorized('Access denied');
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return ApiResponseService::validationError($exception->errors(), 'The given data was invalid.');
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof UnauthorizedHttpException) {
            return $this->handleUnauthorizedHttpException($e, $request);
        }

        return parent::render($request, $e);
    }
}
