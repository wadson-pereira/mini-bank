<?php

namespace App\Http\Responses;

use App\Http\Presenters\BasePresenter;
use App\Http\Presenters\Generics\ErrorPresenter;
use App\Http\Presenters\Modules\Account\Create\SuccessPresenter;
use App\Http\Presenters\Modules\Account\Transaction\DomainErrorPresenter;
use App\Http\Presenters\Modules\Account\Transaction\SuccessPresenter as TransactionSuccessPresenter;
use Domain\Generics\Responses\BaseResponse;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Create\Responses\SuccessResponse;
use Domain\Modules\Account\Transaction\Exceptions\InsufficientFundsException;

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
        if ($useCaseResponse instanceof ErrorResponse && $useCaseResponse->exception instanceof InsufficientFundsException) {
            return new DomainErrorPresenter($useCaseResponse);
        }
        return new ErrorPresenter();
    }
}
