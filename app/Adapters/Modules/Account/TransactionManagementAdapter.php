<?php

namespace App\Adapters\Modules\Account;

use Carbon\Carbon;
use Domain\Modules\Account\Transaction\Collections\TransactionCollection;
use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\ProcessedTransactionEntity;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Enums\ProcessingStatus;
use Domain\Modules\Account\Transaction\Gateways\TransactionManagementGateway;
use Illuminate\Support\Facades\DB;

class TransactionManagementAdapter implements TransactionManagementGateway
{

    public function processTransaction(TransactionEntity $transactionEntity, ProcessingStatus $processingStatus): ProcessedTransactionEntity
    {

        $values = [
            'sender_id' => $transactionEntity->sender->id,
            'receiver_id' => $transactionEntity->receiver->id,
            'amount' => $transactionEntity->amount->value,
            'scheduled_for' => $transactionEntity->scheduledFor,
            'status' => $processingStatus->value,
            'created_at' => Carbon::now()->toDateString(),
            'updated_at' => Carbon::now()->toDateString()
        ];

        if ($transactionEntity->transactionId) {
            $values['id'] = $transactionEntity->transactionId;
        }

        DB::table('account_transaction')->upsert(
            values: $values,
            uniqueBy: [
                'id'
            ],
            update: [
                'status',
                'updated_at'
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

    public function getScheduledTransactionForToday(): TransactionCollection
    {
        $transactions = DB::table('account_transaction')->select([
            'id',
            'receiver_id',
            'sender_id',
            'amount',
        ])->selectRaw("DATE(scheduled_for) as scheduled_for")->whereRaw(
            sprintf("DATE(scheduled_for) = '%s'", Carbon::now()->toDateString())
        )->where('status', '=', ProcessingStatus::SCHEDULED->value)->get()->toArray();

        $collection = new TransactionCollection();
        foreach ($transactions as $transaction) {
            $collection->addEntity(
                new TransactionEntity(
                    transactionId: $transaction->id,
                    sender: new Account(id: $transaction->receiver_id),
                    receiver: new Account(id: $transaction->sender_id),
                    amount: new Amount(
                        value: $transaction->amount
                    ),
                    scheduledFor: $transaction->scheduled_for
                )
            );
        }
        return $collection;
    }
}
