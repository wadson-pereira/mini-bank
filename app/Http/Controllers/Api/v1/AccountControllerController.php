<?php

namespace App\Http\Controllers\Api\v1;

use App\Adapters\Instrumentation\UseCaseInstrumentationAdapter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Account\CreateAccountHttpRequest;
use App\Http\Responses\ResponseFactory;
use Domain\Generics\Logger\Logger;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Create\UseCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountControllerController extends Controller
{
    public function __construct(
        private readonly CreateAccountGateway $createAccountGateway,
        private readonly Logger               $logger
    )
    {
    }

    public function create(CreateAccountHttpRequest $request): JsonResponse
    {
        $createAccountUseCase = new UseCase(
            createAccountGateway: $this->createAccountGateway,
            logger: $this->logger,
            instrumentation: new UseCaseInstrumentationAdapter(useCaseClass: UseCase::class)
        );
        $useCaseResponse = $createAccountUseCase->execute($request->toUseCaseRequest());
        return ResponseFactory::create($useCaseResponse)->present();
    }
}
