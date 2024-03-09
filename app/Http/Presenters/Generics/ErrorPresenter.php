<?php

namespace App\Http\Presenters\Generics;

use App\Http\Presenters\BasePresenter;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorPresenter implements BasePresenter
{
    public function present(): JsonResponse
    {
        return new JsonResponse(
            data: [
                "message" => "Internal Server Error"
            ],
            status: Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
