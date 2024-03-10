<?php

namespace App\Http\Requests\Modules\Account\Transaction;

use App\Http\Requests\Generics\BaseRequest;
use Carbon\Carbon;
use Domain\Modules\Account\Transaction\Entities\Account;
use Domain\Modules\Account\Transaction\Entities\Amount;
use Domain\Modules\Account\Transaction\Entities\TransactionEntity;
use Domain\Modules\Account\Transaction\Request\TransactionRequest;

class CreateTransactionHttpRequest extends BaseRequest
{

    public function rules(): array
    {
        return [
            'sender' => "required|numeric|exists:account,id",
            'receiver' => "required|numeric|exists:account,id",
            'amount' => "required|numeric|min:0.00",
            'scheduled_for' => "date|date_format:Y-m-d|after_or_equal:" . Carbon::now()->toDateString(),
        ];
    }

    public function toUseCaseRequest(): TransactionRequest
    {
        $validated = $this->validated();

        return new TransactionRequest(
            new TransactionEntity(
                transactionId: null,
                sender: new Account(
                    id: $validated['sender']
                ),
                receiver: new Account(
                    id: $validated['receiver']
                ),
                amount: new Amount(
                    value: $validated['amount']
                ),
                scheduledFor: $validated['scheduled_for'] ?? Carbon::now()->toDateString()
            )
        );
    }

    protected function prepareForValidation()
    {

        $this->merge([
            'sender' => $this->route('id')
        ]);
    }
}
