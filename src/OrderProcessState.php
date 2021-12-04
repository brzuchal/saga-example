<?php declare(strict_types=1);

namespace App;

enum OrderProcessState
{
    case ProcessStarted;
    case ProcessClosed;
    case ProcessCancelled;
}
