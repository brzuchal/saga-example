<?php declare(strict_types=1);

namespace App\Command;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

final class CreateShipment
{
    public function __construct(
        public readonly UuidInterface $orderId,
        public readonly ?Money $charge = null,
    ) {
    }
}
