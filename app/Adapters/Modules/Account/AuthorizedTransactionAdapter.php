<?php

namespace App\Adapters\Modules\Account;

use App\Adapters\Gateways\HttpClient\HttpClientGateway;
use Domain\Modules\Account\Transaction\Entities\AuthorizeServiceEnvs;
use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;

class AuthorizedTransactionAdapter implements AuthorizeTransactionGateway
{
    public function __construct(
        private readonly HttpClientGateway $client,
        private readonly AuthorizeServiceEnvs $apiEnv
    )
    {
    }

    public function getTransactionAuthorization(TransactionEntity $transactionEntity): TransactionAuthorization
    {
        try {
            $url = sprintf(
                "%s/%s",
                $this->apiEnv->baseUrl,
                'beta-authorizer'
            );

            $response = $this->client->post($url, [
                'body' => json_encode([
                    "sender" => $transactionEntity->sender->id,
                    "receiver" => $transactionEntity->receiver->id,
                    "amount" => $transactionEntity->amount->value
                ]),
                'header' =>
                    [
                        "Authorization" => sprintf("Bearer %s", $this->apiEnv->authToken),
                        "Content-Type" => "application/json"
                    ]
            ]);
            $body = json_decode($response->getBody()->getContents());
            return new TransactionAuthorization(
                authorized: $body->authorized
            );
        } catch (\Throwable $e) {
            return new TransactionAuthorization(
                authorized: true
            );
        }
    }
}
