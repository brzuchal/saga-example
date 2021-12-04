<?php declare(strict_types=1);

namespace App\Event;

use App\OrderPayment;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

final class OrderCreated
{
    public function __construct(
        public readonly UuidInterface $orderId,
        public readonly Money $totalAmount,
        public readonly OrderPayment $paymentKind,
    ) {}
}
