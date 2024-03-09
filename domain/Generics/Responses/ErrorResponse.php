<?php

namespace Domain\Generics\Responses;

class ErrorResponse extends BaseResponse
{
    public function __construct(public readonly \Throwable $exception)
    {
    }
}
