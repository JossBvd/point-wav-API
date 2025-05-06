<?php
namespace App\Enum;

enum RefundStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case REFUSED = 'REFUSED';
}
