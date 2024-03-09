<?php

namespace Domain\Modules\Account\Create\Rules;

use Domain\Modules\Account\Create\Entities\createdAccountEntity;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Create\Request\CreateAccountRequest;


class CreateAccountRule
{
    public function __construct(private readonly CreateAccountGateway $createAccountGateway)
    {
    }

    public function execute(CreateAccountRequest $request): createdAccountEntity
    {
        return $this->createAccountGateway->createAccount($request->accountEntity);
    }
}
