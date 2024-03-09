<?php

namespace Domain\Modules\Account\Create\Gateways;

use Domain\Modules\Account\Create\Entities\createdAccountEntity;
use Domain\Modules\Account\Create\Entities\NewAccountEntity;

interface CreateAccountGateway
{
    public function createAccount(NewAccountEntity $newAccountEntity): CreatedAccountEntity;
}
