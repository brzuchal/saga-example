<?php declare(strict_types=1);

namespace App\Command;

use Ramsey\Uuid\UuidInterface;

final class CloseOrder
{
    public function __construct(
        protected readonly UuidInterface $orderId,
    ) {
    }
}
