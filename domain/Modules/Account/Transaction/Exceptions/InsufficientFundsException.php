<?php

namespace Domain\Modules\Account\Transaction\Exceptions;

class InsufficientFundsException extends \DomainException
{
    private array $data;

    public function __construct()
    {
        $this->data['amount'] = [
            "Insufficient funds for complete the transaction"
        ];
        parent::__construct();
    }

    public function getData(): array
    {
        return $this->data;
    }
}
