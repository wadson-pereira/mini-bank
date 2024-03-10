<?php

namespace App\Adapters\Modules\Account;

use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Illuminate\Support\Facades\DB;

class TransactionManagementAdpater implements TransactionManagementGateway
{

    public function processTransaction(TransactionEntity $transactionEntity, ProcessingStatus $processingStatus): ProcessedTransactionEntity
    {
        DB::table('account_transaction')->upsert(
            [
                'sender_id' => $transactionEntity->sender->id,
                'receiver_id' => $transactionEntity->receiver->id,
                'amount' => $transactionEntity->amount->value,
                'scheduled_for' => $transactionEntity->scheduledFor,
                'status' => $processingStatus->value
            ],
            [
                'id' => $transactionEntity->transactionId
            ]
        );
        return new ProcessedTransactionEntity(
            sender: $transactionEntity->sender,
            receiver: $transactionEntity->receiver,
            amount: $transactionEntity->amount,
            processingStatus: $processingStatus
        );
    }

    public function getAccountBalance(Account $account): Amount
    {
        $accountBalance = DB::table('account')->select(['balance'])->where([
            'id' => $account->id
        ])->get()->first();

        return new Amount(
            value: $accountBalance->balance
        );
    }

    public function increasesAccountBalance(Account $account, Amount $amount): void
    {
        DB::table('account')->where(['id' => $account->id])->update([
            'balance' => DB::raw("balance + $amount->value")
        ]);
    }

    public function decreaseAccountBalance(Account $account, Amount $amount): void
    {
        DB::table('account')->where(['id' => $account->id])->update([
            'balance' => DB::raw("balance - $amount->value")
        ]);
    }
}
