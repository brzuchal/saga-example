<?php declare(strict_types=1);

namespace App\EventListener;

use App\Event\PaymentCreated;
use App\OrderProcessRepository;

final class AssociateOrderPayment
{
    public function __construct(
        protected readonly OrderProcessRepository $repository,
    ) {
    }

    public function __invoke(PaymentCreated $event): void
    {
        $process = $this->repository->findByOrderId($event->orderId);
        $process->paymentId = $event->paymentId;
        $this->repository->save($process);
    }
}
