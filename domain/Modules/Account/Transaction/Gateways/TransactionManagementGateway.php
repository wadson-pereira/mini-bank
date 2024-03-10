<?php

namespace Domain\Modules\Account\Transaction\Gateways;

use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;

interface TransactionManagementGateway
{
    public function processTransaction(TransactionEntity $transactionEntity, ProcessingStatus $processingStatus): ProcessedTransactionEntity;

    public function getAccountBalance(Account $account): Amount;

    public function increasesAccountBalance(Account $account, Amount $amount): void;

    public function decreaseAccountBalance(Account $account, Amount $amount): void;
}
