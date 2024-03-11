<?php

namespace App\Http\Presenters\Modules\Account\Transaction;

use App\Http\Presenters\BasePresenter;
use Domain\Generics\Gateways\Exception\DomainException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DomainErrorPresenter implements BasePresenter
{
    public function __construct(private readonly DomainException $errorResponse)
    {
    }

    public function present(): JsonResponse
    {
        return new JsonResponse(
            data: $this->errorResponse->getData(),
            status: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
