<?php

namespace App\Adapters\HttpClient;

use App\Adapters\Gateways\HttpClient\HttpClientGateway;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements HttpClientGateway
{
    private $client;

    public function __construct(array $options = [])
    {
        $this->client = new Client($options);
    }

    /**
     * @throws GuzzleException
     */
    public function post(string $uri, array $options = []): ResponseInterface
    {
        return $this->client->post($uri, $options);
    }
}
