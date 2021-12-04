<?php declare(strict_types=1);

namespace App;

use App\Command\CancelOrder;
use App\Command\CloseOrder;
use App\Command\CreatePayment;
use App\Command\CreateShipment;
use App\Command\PaymentRefund;
use App\Event\OrderCancelled;
use App\Event\OrderClosed;
use App\Event\OrderCreated;
use App\Event\PaymentCreated;
use App\Event\PaymentProcessed;
use App\Event\PaymentRefundProcessed;
use App\Event\ShipmentCreated;
use App\Event\ShipmentDelivered;
use Brzuchal\Saga\Attribute\Saga;
use Brzuchal\Saga\Attribute\SagaCompensate;
use Brzuchal\Saga\Attribute\SagaEnd;
use Brzuchal\Saga\Attribute\SagaEventHandler;
use Brzuchal\Saga\Attribute\SagaStart;
use Brzuchal\Saga\SagaLifecycle;
use Money\Money;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Throwable;

#[Saga]
final class OrderProcessing
{
    use HandleTrait;
    protected UuidInterface $orderId;
    protected UuidInterface $paymentId;
    protected Money $totalAmount;
    protected UuidInterface $shipmentId;

    #[SagaStart,SagaEventHandler(associationKey: 'orderId')]
    public function whenCreated(OrderCreated $event): void
    {
        $this->orderId = $event->orderId;
        $this->totalAmount = $event->totalAmount;
        if ($this->totalAmount->isZero()) {
            $this->handle(new CreateShipment(
                orderId: $this->orderId,
            ));
        }
        $this->handle(match ($event->paymentKind) {
            OrderPayment::OnlinePayment => new CreatePayment(
                orderId: $this->orderId,
                amount: $this->totalAmount,
            ),
            OrderPayment::CashOnDelivery => new CreateShipment(
                orderId: $this->orderId,
                charge: $this->totalAmount,
            )
        });
    }

    #[SagaEventHandler(associationKey: 'orderId', property: 'orderId')]
    public function whenPaymentCreated(
        PaymentCreated $event,
        SagaLifecycle $lifecycle,
    ): void {
        $this->paymentId = $event->paymentId;
        $lifecycle->associateValue('paymentId', $event->paymentId);
    }

    #[SagaEventHandler(associationKey: 'paymentId')]
    public function whenPaymentProcessed(
        PaymentProcessed $event,
    ): void {
        $this->handle(new CreateShipment(
            orderId: $this->orderId,
        ));
    }

    #[SagaEventHandler(associationKey: 'orderId')]
    public function whenShipmentCreated(
        ShipmentCreated $event,
        SagaLifecycle $lifecycle,
    ): void {
        $this->shipmentId = $event->shipmentId;
        $lifecycle->associateValue('shipmentId', $event->shipmentId);
    }

    #[SagaEventHandler(associationKey: 'shipmentId')]
    public function whenShipmentDelivered(
        ShipmentDelivered $event,
    ): void {
        $this->handle(new CloseOrder(orderId: $this->orderId));
    }

    #[SagaEnd,SagaEventHandler(associationKey: 'orderId')]
    public function whenClosed(
        OrderClosed $event,
        SagaLifecycle $lifecycle,
    ): void {
        $lifecycle->end();
    }

    #[SagaCompensate('whenCreated')]
    public function handlingOrderCreatedFailure(
        Throwable $throwable,
        SagaLifecycle $lifecycle,
    ): void {
        $this->handle(new CancelOrder($this->paymentId));
        $lifecycle->end();
    }

    #[SagaEventHandler(associationKey: 'orderId')]
    public function whenOrderCancelled(
        OrderCancelled $event,
        SagaLifecycle $lifecycle,
    ): void {
        $lifecycle->end();
    }

    /** @see OrderProcessing::whenPaymentProcessed() */
    #[SagaCompensate('whenPaymentProcessed')]
    public function handlingPaymentProcessedFailure(
        Throwable $throwable,
        SagaLifecycle $lifecycle
    ): void {
        $this->handle(new PaymentRefund(
            paymentId: $this->paymentId,
        ));
        $this->handle(ChatMessage::fromNotification(Notification::fromThrowable(
            $throwable,
        )));
//        $lifecycle->end(); // not yet!
    }

    #[SagaEnd,SagaEventHandler(associationKey: 'paymentId')]
    public function whenPaymentRefundProcessed(
        PaymentRefundProcessed $event,
        SagaLifecycle $lifecycle,
    ): void {
        $notification = new Notification('Order Payment Refunded');
        $notification->content(
            "Order #{$this->orderId->toString()} has been successfully refunded. " .
            "Total refund amount {$this->totalAmount->getAmount()} {$this->totalAmount->getCurrency()}."
        );
        $this->handle(ChatMessage::fromNotification($notification));
//        $lifecycle->end(); // not required!
    }
}
