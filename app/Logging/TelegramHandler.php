<?php


namespace App\Logging;


use Monolog\Handler\TelegramBotHandler;

class TelegramHandler extends TelegramBotHandler
{

    /**
     * Send request to @link https://api.telegram.org/bot on SendMessage action.
     * @param string $message
     */
    protected function send(string $message): void
    {

        try {
            parent::send($message);
        } catch (\RuntimeException $exception) {
            logger()->warning($exception->getMessage());
        }
    }

}
