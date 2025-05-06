<?php
namespace App\Enum;

enum OrderStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case DELIVERED = 'DELIVERED';
    case CANCELED = 'CANCELED';
}
