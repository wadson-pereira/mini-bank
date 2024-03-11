<?php

namespace Domain\Modules\Account\Create\Request;

use Domain\Modules\Account\Create\Entities\NewAccountEntity;

class CreateAccountRequest
{
    public function __construct(public readonly NewAccountEntity $accountEntity)
    {
    }
}
