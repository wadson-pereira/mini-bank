<?php

namespace Domain\Modules\Account\Transaction\Rules;

use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;
use Domain\Modules\Account\Transaction\Exceptions\InsufficientFundsException;
use Domain\Modules\Account\Transaction\Exceptions\TransactionWasNotAuthorized;
use Domain\Modules\Account\Transaction\Gateways\AuthorizeTransactionGateway;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Domain\Modules\Account\Transaction\Request\TransactionRequest;

class ProcessingTransactionRule
{
    public function __construct(private readonly TransactionManagementGateway $transactionManagementGateway, private readonly AuthorizeTransactionGateway $authorizeTransactionGateway)
    {
    }

    public function execute(TransactionRequest $request): ProcessedTransactionEntity
    {
        $transaction = $request->transactionEntity;
        $processingStatus = $this->getTransactionProcessingStatus($transaction);

        $processedTransactionEntity = $this->transactionManagementGateway->processTransaction(transactionEntity: $transaction, processingStatus: $processingStatus);

        if ($processedTransactionEntity->processingStatus === ProcessingStatus::INSUFFICIENT_FUNDS) {
            throw new InsufficientFundsException();
        }
        if ($processedTransactionEntity->processingStatus === ProcessingStatus::UNAUTHORIZED) {
            throw new TransactionWasNotAuthorized();
        }

        if ($processedTransactionEntity->processingStatus === ProcessingStatus::COMPLETED) {
            $this->transactionManagementGateway->increasesAccountBalance($processedTransactionEntity->receiver, $processedTransactionEntity->amount);
            $this->transactionManagementGateway->decreaseAccountBalance($processedTransactionEntity->sender, $processedTransactionEntity->amount);
        }
        return $processedTransactionEntity;
    }

    private function getTransactionProcessingStatus(TransactionEntity $transactionEntity): ProcessingStatus
    {
        $scheduledTransaction = $this->verifyScheduledTransaction($transactionEntity);

        if ($scheduledTransaction) {
            return ProcessingStatus::SCHEDULED;
        }

        $authorizedTransaction = $this->verifyAuthorizedTransaction(transactionEntity: $transactionEntity);

        if (!$authorizedTransaction) {
            return ProcessingStatus::UNAUTHORIZED;
        }

        $hasEnoughBalance = $this->verifyAccountHasEnoughBalance(senderAccount: $transactionEntity->sender, amount: $transactionEntity->amount);

        if (!$hasEnoughBalance) {
            return ProcessingStatus::INSUFFICIENT_FUNDS;
        }

        return ProcessingStatus::COMPLETED;
    }

    private function verifyScheduledTransaction(TransactionEntity $transactionEntity): bool
    {
        return $transactionEntity->transactionId === null && (
                strtotime($transactionEntity->scheduledFor) >= strtotime(date('Y-m-d', strtotime('+1 day'))));
    }

    private function verifyAuthorizedTransaction(TransactionEntity $transactionEntity): bool
    {
        $authorization = $this->authorizeTransactionGateway->getTransactionAuthorization($transactionEntity);

        return $authorization->authorized;
    }

    private function verifyAccountHasEnoughBalance(Account $senderAccount, Amount $amount): bool
    {
        $balance = $this->transactionManagementGateway->getAccountBalance($senderAccount);
        return (($balance->value - $amount->value) >= 0.00);
    }
}
