<?php

namespace Domain\Modules\Account\Transaction\Exceptions;

use Domain\Generics\Gateways\Exception\DomainException;

class TransactionWasNotAuthorized extends \DomainException implements DomainException
{
    private array $data;

    public function __construct()
    {
        $this->data['transaction'] = [
            "The transaction was not authorized by authorization authority"
        ];
        parent::__construct();
    }

    public function getData(): array
    {
        return $this->data;
    }
}
