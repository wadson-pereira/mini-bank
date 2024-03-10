<?php

namespace Domain\Modules\Account\Transaction\Enums;

enum ProcessingStatus: string
{
    case  SCHEDULED = "scheduled";
    case  UNAUTHORIZED = "unauthorized";
    case  COMPLETED = "completed";
    case  INSUFFICIENT_FUNDS = "insufficient_funds";
    case  FAILED = "failed";
}
