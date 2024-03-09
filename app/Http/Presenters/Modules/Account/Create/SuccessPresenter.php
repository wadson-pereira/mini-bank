<?php

namespace App\Http\Presenters\Modules\Account\Create;

use App\Http\Presenters\BasePresenter;
use Domain\Modules\Account\Create\Responses\SuccessResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuccessPresenter implements BasePresenter
{
    public function __construct(private readonly SuccessResponse $successResponse)
    {
    }

    public function present(): JsonResponse
    {
        return new JsonResponse(
            data: [
                'account_id' => $this->successResponse->createdAccount
            ],
            status: Response::HTTP_CREATED
        );
    }
}
