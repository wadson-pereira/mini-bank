<?php

namespace Domain\Modules\Account\Transaction\Entities;

use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;

class ProcessedTransactionEntity
{
    public function __construct(
        public readonly Account          $sender,
        public readonly Account          $receiver,
        public readonly Amount           $amount,
        public readonly ProcessingStatus $processingStatus
    )
    {

    }
}
