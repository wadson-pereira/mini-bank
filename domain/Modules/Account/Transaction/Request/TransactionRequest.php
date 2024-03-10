<?php

namespace Domain\Modules\Account\Transaction\Request;

use Domain\Modules\Account\Transaction\Entities\TransactionEntity;

class TransactionRequest
{
    public function __construct(public readonly TransactionEntity $transactionEntity)
    {

    }
}
