<?php

namespace App\Http\Presenters;

use Symfony\Component\HttpFoundation\JsonResponse;

interface BasePresenter
{
    public function present(): JsonResponse;
}
