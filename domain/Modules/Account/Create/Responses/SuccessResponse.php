<?php

namespace Domain\Modules\Account\Create\Responses;

use Domain\Generics\Responses\BaseResponse;
use Domain\Modules\Account\Create\Entities\createdAccountEntity;

class SuccessResponse extends BaseResponse
{
    public function __construct(public readonly createdAccountEntity $createdAccount)
    {
    }
}
