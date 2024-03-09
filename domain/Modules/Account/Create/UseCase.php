<?php

namespace Domain\Modules\Account\Create;

use Domain\Generics\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Logger\Logger;
use Domain\Generics\Responses\BaseResponse;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Create\Gateways\CreateAccountGateway;
use Domain\Modules\Account\Create\Request\CreateAccountRequest;
use Domain\Modules\Account\Create\Responses\SuccessResponse;
use Domain\Modules\Account\Create\Rules\CreateAccountRule;

class UseCase
{
    private CreateAccountRule $rule;


    public function __construct(
        private CreateAccountGateway            $createAccountGateway,
        private readonly Logger                 $logger,
        private readonly UseCaseInstrumentation $instrumentation
    )
    {
        $this->rule = new CreateAccountRule($this->createAccountGateway);
    }

    public function execute(CreateAccountRequest $requestEntity): BaseResponse
    {
        try {
            $this->instrumentation->useCaseStarted();
            $createdSale = $this->rule->execute($requestEntity);
            $this->instrumentation->useCaseFinished();
            return new SuccessResponse($createdSale);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), context: [$exception]);
            $this->instrumentation->useCaseFailed($exception);
            return new ErrorResponse($exception);
        }
    }
}
