<?php

namespace Domain\Modules\Account\Transaction\Responses;

use Domain\Generics\Responses\BaseResponse;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;

class SuccessResponse extends BaseResponse
{
    public function __construct(public readonly ProcessedTransactionEntity $processedTransactionEntity)
    {
    }
}
