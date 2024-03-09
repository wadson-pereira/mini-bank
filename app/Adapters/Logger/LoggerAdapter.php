<?php

namespace App\Adapters\Logger;

use Domain\Generics\Logger\Logger;
use Illuminate\Support\Facades\Log;

class LoggerAdapter implements Logger
{
    public function info(string $message, array $context = []): void
    {
        Log::log('info', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::log('error', $message, $context);
    }
}
