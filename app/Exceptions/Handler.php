<?php

namespace App\Exceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger;

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
     * Returns error message from exeption for sending notification
     * @param \Exception $exception
     * @return string
     */
    protected function getErrorLogMessageForNotify(\Exception $exception): string
    {
        $message = "\n";
        $message .= 'Url: '.request()->fullUrl()."\n";
        $message .= 'Error: '.$exception->getMessage()."\n\n";
        $message .= 'Error Code: '.$exception->getCode()."\n";
        $message .= 'File: '.$exception->getFile()."\n";
        $message .= 'Line: '.$exception->getLine()."\n\n";
        $message .= 'UserAgent: '.request()->userAgent()."\n";
        $message .= 'IP: '.request()->getClientIp()."\n\n";
        $message .= "Request data \n".json_encode(request()->all())."\n\n";
        $message .= "Trace: \n".implode("\n\n", array_slice(explode("\n", $exception->getTraceAsString()), 0, 3))."\n";
        $message = substr($message, 0, 4000);
        return $message;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(\Exception $exception)
    {
        if (\App::environment() === 'production' && app()->bound('sentry') && $this->shouldReport($exception)) {
            //log to sentry in production only
            app('sentry')->captureException($exception);
            // create a log Telegram
            try {
                $log = new Logger('Odin');
                $handler = new TelegramBotHandler(env('TG_BOT_KEY'), config('logging.tg_channel'));
                $handler->setFormatter(new \Monolog\Formatter\LineFormatter(
                    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
                    null, // Datetime format
                    true, // allowInlineLineBreaks option, default false
                    true  // ignoreEmptyContextAndExtra option, default false
                ));
                $log->pushHandler($handler);
                $log->error($this->getErrorLogMessageForNotify($exception));
            } catch (\Exception $e) {
                logger()->warning($e->getMessage());
            }
        } else {
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
    public function render($request, \Exception $exception)
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
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 404);
            case ProviderNotFoundException::class:
                return response()->json([
                    'error' => [
                        'code'      => $exception->getCode(),
                        'message'   => $exception->getMessage(),
                        'phrase'    => $exception->getPhrase()
                    ]
                ], 404);
            case CustomerUpdateException::class:
            case OrderUpdateException::class:
                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
                ], 500);
            case PaymentException::class:
                return response()->json([
                    'error' => [
                        'code'      => $exception->getCode(),
                        'message'   => $exception->getMessage(),
                        'phrase'    => $exception->getPhrase()
                    ]
                ], 500);
            case PPCurrencyNotSupportedException::class:
                $message = json_decode($exception->getMessage(), true);

                return response()->json([
                    'error' => ['code' => $exception->getCode(), 'message' => $message]
                ], 200);
            case BlockEmailException::class:
                return response()->json($exception->getParams(), 200);
            default:
                return parent::render($request, $exception);
        endswitch;
    }
}
