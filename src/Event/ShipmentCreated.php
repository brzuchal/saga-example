<?php declare(strict_types=1);

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final class ShipmentCreated
{
    public function __construct(
        public readonly UuidInterface $shipmentId,
        public readonly UuidInterface $orderId,
    ) {
    }
}
