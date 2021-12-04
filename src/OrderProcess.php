<?php declare(strict_types=1);

namespace App;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

final class OrderProcess
{
    public readonly UuidInterface $paymentId;
    public readonly UuidInterface $shipmentId;
    public OrderProcessState $state = OrderProcessState::ProcessStarted;

    public function __construct(
        public readonly UuidInterface $orderId,
        public readonly Money $totalAmount,
    ) {
    }

    public function close(): void
    {
        $this->state = OrderProcessState::ProcessClosed;
    }

    public function cancel(): void
    {
        $this->state = OrderProcessState::ProcessCancelled;
    }
}
