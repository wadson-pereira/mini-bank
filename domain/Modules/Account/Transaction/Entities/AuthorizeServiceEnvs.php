<?php

namespace Domain\Modules\Account\Transaction\Entities;

class AuthorizeServiceEnvs
{
    public function __construct(public readonly string $baseUrl, public readonly string $authToken)
    {
    }
}
