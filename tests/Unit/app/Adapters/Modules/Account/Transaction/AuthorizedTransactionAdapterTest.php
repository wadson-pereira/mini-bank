<?php

namespace Tests\Unit\app\Adapters\Modules\Account\Transaction;

use App\Adapters\Gateways\HttpClient\HttpClientGateway;
use App\Adapters\Modules\Account\TransactionAuthorizerAdapter;
use Carbon\Carbon;
use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\AuthorizeServiceEnvs;
use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

class AuthorizedTransactionAdapterTest extends TestCase
{

    private AuthorizeServiceEnvs $envs;
    private TransactionAuthorizerAdapter $adapter;
    private HttpClientGateway $httpClientGateway;
    private ResponseInterface $responseInterfaceMock;
    private StreamInterface $streamInterfaceMock;

    public function testShouldCallExternalServiceWithSuccess()
    {
        $transactionEntity = new TransactionEntity(
            transactionId: 1,
            sender: new Account(id: 1),
            receiver: new Account(
                id: 2
            ),
            amount: new Amount(1.00),
            scheduledFor: Carbon::now()->toDateString()
        );

        $expectedAuthorization = new TransactionAuthorization(
            false
        );

        $this->streamInterfaceMock->shouldReceive('getContents')->andReturn(
            json_encode(["authorized" => $expectedAuthorization->authorized])
        )->once();

        $this->responseInterfaceMock->shouldReceive('getBody')->andReturn($this->streamInterfaceMock)->once();

        $this->httpClientGateway->shouldReceive('post')->withArgs(["{$this->envs->baseUrl}/beta-authorizer", [
                'body' => json_encode([
                    "sender" => $transactionEntity->sender->id,
                    "receiver" => $transactionEntity->receiver->id,
                    "amount" => $transactionEntity->amount->value
                ]),
                'header' =>
                    [
                        "Authorization" => "Bearer {$this->envs->authToken}",
                        "Content-Type" => "application/json"
                    ]
            ]]
        )->andReturn($this->responseInterfaceMock)->once();

        $authorization = $this->adapter->getTransactionAuthorization($transactionEntity);
        $this->assertEquals($expectedAuthorization, $authorization);
    }

    protected function setUp(): void
    {
        $this->httpClientGateway = \Mockery::mock(HttpClientGateway::class);
        $this->responseInterfaceMock = \Mockery::mock(ResponseInterface::class);
        $this->streamInterfaceMock = \Mockery::mock(StreamInterface::class);
        $this->expectedServiceJsonResponse = [
            "results" => [
                "sunrise" => "11:19:48 PM",
                "sunset" => "9:33:48 AM",
            ],
            "status" => "OK",
            "tzid" => "UTC"
        ];
        $this->envs = new AuthorizeServiceEnvs("teste", 1);
        $this->adapter = new TransactionAuthorizerAdapter($this->httpClientGateway, $this->envs);
    }
}
