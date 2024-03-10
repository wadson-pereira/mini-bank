<?php

namespace Domain\Modules\Account\Transaction\Entities;

class Amount
{
    public function __construct(public readonly float $value)
    {
    }
}
