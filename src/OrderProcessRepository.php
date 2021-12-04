<?php declare(strict_types=1);

namespace App;

use Ramsey\Uuid\UuidInterface;

interface OrderProcessRepository
{
    public function findByOrderId(UuidInterface $orderId): ?OrderProcess;
    public function findByPaymentId(UuidInterface $paymentId): ?OrderProcess;
    public function findByShipmentId(UuidInterface $shipmentId): ?OrderProcess;
    public function save(OrderProcess $process): void;
}
