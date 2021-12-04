<?php declare(strict_types=1);

namespace App;

enum OrderPayment
{
    case OnlinePayment;
    case CashOnDelivery;
}
