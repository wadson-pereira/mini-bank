<?php

namespace Domain\Modules\Account\Transaction\Collections;

use Domain\Generics\Collection\Collection;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;

class TransactionCollection extends Collection
{
    public function addEntity(TransactionEntity $transactionEntity): static
    {
        return parent::add($transactionEntity);
    }

    public function all(): array
    {
        return parent::all();
    }
}
