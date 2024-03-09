<?php

namespace App\Http\Responses;

use App\Http\Presenters\BasePresenter;
use App\Http\Presenters\Generics\ErrorPresenter;
use App\Http\Presenters\Modules\Account\Create\SuccessPresenter;
use Domain\Generics\Responses\BaseResponse;
use Domain\Modules\Account\Create\Responses\SuccessResponse;

class ResponseFactory
{
    public static function create(BaseResponse $useCaseResponse): BasePresenter
    {

        if ($useCaseResponse instanceof SuccessResponse) {
            return new SuccessPresenter($useCaseResponse);
        }
        return new ErrorPresenter();
    }
}
