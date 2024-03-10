<?php

namespace Domain\Modules\Account\Transaction;

use Domain\Generics\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Logger\Logger;
use Domain\Generics\Responses\BaseResponse;
use Domain\Generics\Responses\ErrorResponse;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Domain\Modules\Account\Transaction\Request\TransactionRequest;
use Domain\Modules\Account\Transaction\Responses\SuccessResponse;
use Domain\Modules\Account\Transaction\Rules\ProcessingTransactionRule;

class UseCase
{
    private ProcessingTransactionRule $processingTransactionRule;


    public function __construct(
        private readonly TransactionManagementGateway $transactionManagementGateway,
        private readonly AuthorizeTransactionGateway  $authorizeTransactionGateway,
        private readonly Logger                       $logger,
        private readonly UseCaseInstrumentation       $instrumentation
    )
    {
        $this->processingTransactionRule = new ProcessingTransactionRule($this->transactionManagementGateway, $this->authorizeTransactionGateway);
    }

    public function execute(TransactionRequest $request): BaseResponse
    {
        try {
            $this->instrumentation->useCaseStarted();
            $processedTransaction = $this->processingTransactionRule->execute($request);
            $this->instrumentation->useCaseFinished();
            return new SuccessResponse($processedTransaction);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), context: [$exception]);
            $this->instrumentation->useCaseFailed($exception);
            return new ErrorResponse($exception);
        }
    }
}
