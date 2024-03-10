<?php

namespace App\Adapters\Gateways\HttpClient;

use Psr\Http\Message\ResponseInterface;

interface HttpClientGateway
{
    public function post(string $uri, array $options = []): ResponseInterface;
}
