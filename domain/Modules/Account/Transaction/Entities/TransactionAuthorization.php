<?php

namespace Domain\Modules\Account\Transaction\Entities;

class TransactionAuthorization
{
    public function __construct(public readonly bool $authorized)
    {
    }
}
