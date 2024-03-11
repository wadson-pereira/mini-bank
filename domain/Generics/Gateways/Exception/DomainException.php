<?php

namespace Domain\Generics\Gateways\Exception;

interface DomainException
{
    public function getData(): array;
}
