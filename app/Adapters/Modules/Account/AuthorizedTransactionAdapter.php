<?php

namespace App\Adapters\Modules\Account;

use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;

class AuthorizedTransactionAdapter implements AuthorizeTransactionGateway
{

    public function getTransactionAuthorization(TransactionEntity $newAccountEntity): TransactionAuthorization
    {
        return new TransactionAuthorization(
            authorized: true
        );
    }
}
