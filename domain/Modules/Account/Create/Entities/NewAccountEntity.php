<?php

namespace Domain\Modules\Account\Create\Entities;

class NewAccountEntity
{
    public function __construct(public readonly string $name, public readonly float $balance)
    {
    }
}
