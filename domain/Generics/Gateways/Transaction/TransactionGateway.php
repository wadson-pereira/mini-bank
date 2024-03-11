<?php

namespace Domain\Generics\Gateways\Transaction;

interface TransactionGateway
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
