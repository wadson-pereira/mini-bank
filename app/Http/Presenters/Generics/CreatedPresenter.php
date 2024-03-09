<?php

namespace App\Http\Presenters\Generics;

use App\Http\Presenters\BasePresenter;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreatedPresenter implements BasePresenter
{
    public function present(): JsonResponse
    {
        return new JsonResponse(
            status: Response::HTTP_CREATED
        );
    }
}
