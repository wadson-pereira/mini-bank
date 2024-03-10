<?php

namespace App\Http\Requests\Modules\Account\Create;

use App\Http\Requests\Generics\BaseRequest;
use Domain\Modules\Account\Create\Entities\NewAccountEntity;
use Domain\Modules\Account\Create\Request\CreateAccountRequest;


class CreateAccountHttpRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'balance' => "required|numeric|min:0.00",
        ];
    }

    public function toUseCaseRequest(): CreateAccountRequest
    {
        $validated = $this->validated();

        return new CreateAccountRequest(new NewAccountEntity(
                name: $validated['name'],
                balance: $validated['balance']
            )
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->get('balance') === null) {
            $this->merge([
                'balance' => 0
            ]);
        }
    }
}
