<?php

namespace Domain\Generics\Gateways\Logger;

interface Logger
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}
