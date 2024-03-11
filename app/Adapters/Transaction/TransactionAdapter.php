<?php

namespace App\Adapters\Transaction;

use Domain\Generics\Gateways\Transaction\TransactionGateway;
use Illuminate\Support\Facades\DB;

class TransactionAdapter implements TransactionGateway
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollBack();
    }
}
