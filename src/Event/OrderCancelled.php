<?php declare(strict_types=1);

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final class OrderCancelled
{
    public function __construct(
        protected readonly UuidInterface $orderId,
    ) {
    }

}
