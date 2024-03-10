<?php

namespace Domain\Modules\Account\Transaction\Entities;

class CreatedTransactionEntity
{
    public function __construct(public readonly int $transactionId)
    {
    }
}
