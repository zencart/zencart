<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // Check if the exception is a NotFoundHttpException (route not found)
        if ($exception instanceof NotFoundHttpException) {
            // You can handle this exception in any way you want. Here, we are throwing a custom exception.
            throw new \Exception('Route not found.', 404);
        }

        // Continue with the default exception handling for other types of exceptions.
        return parent::render($request, $exception);
    }
    public function report(Throwable $exception)
    {
        // Prevent Laravel's default exception handling and logging for specific exceptions (e.g., NotFoundHttpException).
        // Add any other exceptions you want to bypass the default handling.
        if ($exception instanceof NotFoundHttpException) {
            throw $exception;
        }
        parent::report($exception);
    }
}
