<?php declare(strict_types=1);

namespace App\EventListener;

use App\Event\OrderClosed;
use App\OrderProcessRepository;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class FinishProcessingOrder
{
    use HandleTrait;
    public function __construct(
        private MessageBusInterface $messageBus,
        protected readonly OrderProcessRepository $repository,
    ) {
    }

    public function __invoke(OrderClosed $event): void
    {
        $process = $this->repository->findByOrderId($event->orderId);
        $process->close();
        $this->repository->save($process);
    }
}
