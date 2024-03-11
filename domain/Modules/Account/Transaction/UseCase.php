<?php

namespace Domain\Modules\Account\Transaction;

use Domain\Generics\Gateways\Instrumentation\UseCaseInstrumentation;
use Domain\Generics\Gateways\Logger\Logger;
use Domain\Generics\Gateways\Transaction\TransactionGateway;
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
        private readonly AuthorizeTransactionGateway $authorizeTransactionGateway,
        private readonly TransactionGateway          $transactionGateway,
        private readonly Logger                      $logger,
        private readonly UseCaseInstrumentation      $instrumentation
    )
    {
        $this->processingTransactionRule = new ProcessingTransactionRule($this->transactionManagementGateway, $this->authorizeTransactionGateway);
    }

    public function execute(TransactionRequest $request): BaseResponse
    {
        try {
            $this->instrumentation->useCaseStarted();
            $this->transactionGateway->begin();
            $processedTransaction = $this->processingTransactionRule->execute($request);
            $this->transactionGateway->commit();
            $this->instrumentation->useCaseFinished();
            return new SuccessResponse($processedTransaction);
        } catch (\Throwable $exception) {
            $this->transactionGateway->rollback();
            $this->logger->error($exception->getMessage(), context: [$exception]);
            $this->instrumentation->useCaseFailed($exception);
            return new ErrorResponse($exception);
        }
    }
}
