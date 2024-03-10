<?php

namespace Domain\Modules\Account\Transaction\Entities;

class TransactionEntity
{
    public function __construct(
        public readonly ?int    $transactionId,
        public readonly Account $sender,
        public readonly Account $receiver,
        public readonly Amount  $amount,
        public readonly string  $scheduledFor
    )
    {
    }
}
