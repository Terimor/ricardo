<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CustomerUpdateException;
use App\Exceptions\InvalidParamsException;
use App\Exceptions\OrderUpdateException;
use App\Exceptions\PaymentException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        // log to sentry
        if (env('APP_ENV') != 'local') {
            if (app()->bound('sentry') && $this->shouldReport($exception)) {
                app('sentry')->captureException($exception);
            }
        }
        //remove log to file on remote server
        if (env('APP_ENV') == 'local') {
            parent::report($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof InvalidParamsException) {
            return response()->json([
                'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            ], 400);
        } else if ($exception instanceof ProductNotFoundException) {
            return response()->json([
                'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            ], 404);
        } else if ($exception instanceof CustomerUpdateException) {
            return response()->json([
                'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            ], 500);
        } else if ($exception instanceof OrderUpdateException) {
            return response()->json([
                'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            ], 500);
        } else if ($exception instanceof PaymentException) {
            return response()->json([
                'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            ], 500);
        }
        return parent::render($request, $exception);
    }
}
