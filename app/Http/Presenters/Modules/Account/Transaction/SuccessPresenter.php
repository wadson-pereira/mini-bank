<?php

namespace App\Http\Presenters\Modules\Account\Transaction;

use App\Http\Presenters\BasePresenter;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuccessPresenter implements BasePresenter
{
    public function __construct()
    {
    }

    public function present(): JsonResponse
    {
        return new JsonResponse(
            status: Response::HTTP_ACCEPTED
        );
    }
}
