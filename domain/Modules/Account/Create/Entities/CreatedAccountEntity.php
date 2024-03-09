<?php

namespace Domain\Modules\Account\Create\Entities;

class CreatedAccountEntity
{
    public function __construct(public readonly int $id)
    {
    }
}
