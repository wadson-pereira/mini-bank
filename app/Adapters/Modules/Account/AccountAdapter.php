<?php

namespace App\Adapters\Modules\Account;

use Domain\Modules\Account\Create\Entities\CreatedAccountEntity;
use Domain\Modules\Account\Create\Entities\NewAccountEntity;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Illuminate\Support\Facades\DB;

class AccountAdapter implements CreateAccountGateway
{
    public function createAccount(NewAccountEntity $newAccountEntity): CreatedAccountEntity
    {
        $accountId = DB::table('account')->insertGetId(
            [
                'name' => $newAccountEntity->name,
                'balance' => $newAccountEntity->balance
            ]
        );
        return new CreatedAccountEntity(id: $accountId);
    }
}
