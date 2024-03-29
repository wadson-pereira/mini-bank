<?php

namespace Domain\Modules\Account\Transaction\Exceptions;

use Domain\Generics\Gateways\Exception\DomainException;

class InsufficientFundsException extends \DomainException implements DomainException
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
