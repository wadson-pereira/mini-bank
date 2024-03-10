<?php

namespace Domain\Modules\Account\Transaction\Gateways;

use Domain\Modules\Account\Transaction\Entities\TransactionAuthorization;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;

interface AuthorizeTransactionGateway
{
    public function getTransactionAuthorization(TransactionEntity $newAccountEntity): TransactionAuthorization;
}
