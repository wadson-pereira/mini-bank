<?php

namespace App\Http\Responses;

use App\Http\Presenters\BasePresenter;
use App\Http\Presenters\Generics\ErrorPresenter;
use App\Http\Presenters\Modules\Account\Create\SuccessPresenter;
use App\Http\Presenters\Modules\Account\Transaction\DomainErrorPresenter;
use App\Http\Presenters\Modules\Account\Transaction\SuccessPresenter as TransactionSuccessPresenter;
use Domain\Generics\Gateways\Exception\DomainException;
use Domain\Generics\Responses\BaseResponse;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Create\Responses\SuccessResponse;

class ResponseFactory
{
    public static function create(BaseResponse $useCaseResponse): BasePresenter
    {

        if ($useCaseResponse instanceof SuccessResponse) {
            return new SuccessPresenter($useCaseResponse);
        }

        if ($useCaseResponse instanceof \Domain\Modules\Account\Transaction\Responses\SuccessResponse) {
            return new TransactionSuccessPresenter();
        }
        if ($useCaseResponse instanceof ErrorResponse && $useCaseResponse->exception instanceof DomainException) {
            return new DomainErrorPresenter($useCaseResponse->exception);
        }
        return new ErrorPresenter();
    }
}
