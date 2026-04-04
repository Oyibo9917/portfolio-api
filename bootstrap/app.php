<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (MethodNotAllowedHttpException $e) {
            // Optional logging or reporting
            // Log::warning('Route not found: ' . $e->getMessage());
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return response()->json([
                'error' => 'Method Not Allowed',
                'message' => 'The requested method is not supported for this route. Please check the API documentation for valid methods.',
            ], 405);
        });

        $exceptions->report(function (NotFoundHttpException $e) {
            // Optional logging or reporting
            // Log::warning('Route not found: ' . $e->getMessage());
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'error' => 'Route Not Found',
                'message' => 'The requested route could not be found on the server. Please check the URL and try again.',
            ], 404);
        });

        // Handle 401 Unauthorized - Unauthenticated user or invalid token
        $exceptions->report(function (AuthenticationException $e) {
            // Log or report the authentication issue
            // Log::error('Unauthenticated: ' . $e->getMessage());
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'You must be logged in to access this resource. Please authenticate and try again.',
            ], 401);
        });

        // Handle TokenMismatchException for Sanctum token expiration or issues
        $exceptions->report(function (TokenMismatchException $e) {
            // Log or report token mismatch
            // Log::error('Token Mismatch: ' . $e->getMessage());
        });

        $exceptions->render(function (TokenMismatchException $e) {
            return response()->json([
                'error' => 'Token Mismatch',
                'message' => 'Your session has expired, or the token is invalid. Please log in again to continue.',
            ], 401);
        });

        // Handle 422 Validation errors (e.g., invalid input data)
        $exceptions->report(function (ValidationException $e) {
            // Log validation errors or report externally
            // Log::info('Validation failed: ' . $e->getMessage());
        });

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => 'There were one or more issues with your request.',
                'details' => $e->errors(), // Return detailed validation errors
            ], 422);
        });

        // Handle Forbidden errors (e.g., insufficient permissions)
        $exceptions->report(function (AccessDeniedHttpException $e) {
            // Optional logging of access denial
            // Log::warning('Access denied: ' . $e->getMessage());
        });

        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource. Please contact the administrator if you believe this is an error.',
            ], 403);
        });

        // Handle ModelNotFoundException (e.g., resource not found in database)
        $exceptions->report(function (ModelNotFoundException $e) {
            // Optionally log missing model errors
            // Log::warning('Model not found: ' . $e->getMessage());
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'The requested resource could not be found. Please check the provided ID or resource details.',
            ], 404);
        });
    })->create();
