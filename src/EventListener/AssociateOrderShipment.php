<?php declare(strict_types=1);

namespace App\EventListener;

use App\Event\ShipmentCreated;
use App\OrderProcessRepository;

final class AssociateOrderShipment
{
    public function __construct(
        protected readonly OrderProcessRepository $repository,
    ) {
    }

    public function __invoke(ShipmentCreated $event): void
    {
        $process = $this->repository->findByOrderId($event->orderId);
        $process->shipmentId = $event->shipmentId;
        $this->repository->save($process);
    }
}
