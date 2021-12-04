<?php declare(strict_types=1);

namespace App\EventListener;

use App\Command\CreatePayment;
use App\Command\CreateShipment;
use App\Event\OrderCreated;
use App\OrderPayment;
use App\OrderProcess;
use App\OrderProcessRepository;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class StartProcessingOrder
{
    use HandleTrait;
    public function __construct(
        private MessageBusInterface $messageBus,
        protected readonly OrderProcessRepository $repository,
    ) {
    }

    public function __invoke(OrderCreated $event): void
    {
        $process = new OrderProcess(
            orderId: $event->orderId,
            totalAmount: $event->totalAmount,
        );
        $this->repository->save($process);
        if ($event->totalAmount->isZero()) {
            $this->handle(new CreateShipment(
                orderId: $process->orderId,
            ));
        }
        $this->handle(match ($event->paymentKind) {
            OrderPayment::OnlinePayment => new CreatePayment(
                orderId: $process->orderId,
                amount: $process->totalAmount,
            ),
            OrderPayment::CashOnDelivery => new CreateShipment(
                orderId: $process->orderId,
                charge: $process->totalAmount,
            )
        });
    }
}
