<?php declare(strict_types=1);

namespace App\EventListener;

use App\Command\CreateShipment;
use App\Event\PaymentProcessed;
use App\OrderProcessRepository;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class StartShipmentOnPaymentProcessed
{
    use HandleTrait;
    public function __construct(
        private MessageBusInterface $messageBus,
        protected readonly OrderProcessRepository $repository,
    ) {
    }

    public function __invoke(PaymentProcessed $event): void
    {
        $process = $this->repository->findByPaymentId($event->paymentId);
        $this->handle(new CreateShipment(
            orderId: $process->orderId,
        ));
    }
}
