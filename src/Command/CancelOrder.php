<?php declare(strict_types=1);

namespace App\Command;

use Ramsey\Uuid\UuidInterface;

final class CancelOrder
{
    public function __construct(
        public readonly UuidInterface $orderId)
    {
    }
}
