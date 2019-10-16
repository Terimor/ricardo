<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\ProviderNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    const ECODE_AUTH                    = 10401;
    const ECODE_PRODUCT_NOT_FOUND       = 10001;
    const ECODE_CUSTOMER_UPDATE         = 10002;
    const ECODE_ORDER_UPDATE            = 10003;
    const ECODE_INVALID_PARAMS          = 10004;
    const ECODE_PAYMENT                 = 10005;
    const ECODE_ORDER_NOT_FOUND         = 10006;
    const ECODE_TXN_NOT_FOUND           = 10007;
    const ECODE_PP_CUR_NOT_SUPPORTED    = 10008;
    const ECODE_PROVIDER_NOT_FOUND      = 10009;

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
        if (in_array(env('ENVIRONMENT'), ['production'])) {
            if (app()->bound('sentry') && $this->shouldReport($exception)) {
                app('sentry')->captureException($exception);
            }
        } else {
            //remove log to file on remote server
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
        $class = get_class($exception);
        switch ($class):
            case InvalidParamsException::class:
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 400);
            case AuthException::class:
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 401);
            case OrderNotFoundException::class:
            case ProductNotFoundException::class:
            case TxnNotFoundException::class:
            case ProviderNotFoundException::class:
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 404);
            case CustomerUpdateException::class:
            case OrderUpdateException::class:
            case PaymentException::class:
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 500);
            case PPCurrencyNotSupportedException::class:
                $message = json_decode($exception->getMessage(), true);

                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $message]
                ], 200);
            default:
                return parent::render($request, $exception);
        endswitch;
    }
}
