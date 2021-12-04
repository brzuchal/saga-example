<?php declare(strict_types=1);

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final class OrderClosed
{
    public function __construct(
        public readonly UuidInterface $orderId,
    ) {
    }
}
