<?php

declare(strict_types=1);

namespace spec\Setono\SyliusReserveStockPlugin\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\SyliusReserveStockPlugin\Listener\UpdateCartOnPageviewListener;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

final class UpdateCartOnPageviewListenerSpec extends ObjectBehavior
{
    function let(
        CartContextInterface $cartContext,
        OrderInterface $order,
        ObjectManager $orderManager
    ): void {
        $cartContext->getCart()->willReturn($order);

        $this->beConstructedWith(
            $cartContext,
            $orderManager
        );
    }

    function is_is_the_right_listener(): void
    {
        $this->shouldImplement(UpdateCartOnPageviewListener::class);
    }

    function it_should_not_work_when_no_master_request(
        CartContextInterface $cartContext,
        PostResponseEvent $event
    ): void {
        $cartContext->getCart()->shouldNotBeCalled();
        $event->isMasterRequest()->shouldBeCalled()->willReturn(false);

        $this->onKernelTerminate($event)->shouldReturn(null);
    }

    function it_should_not_work_when_no_cart_found(CartContextInterface $cartContext, PostResponseEvent $event): void
    {
        $cartContext->getCart()->shouldBeCalled()->willThrow(new CartNotFoundException());
        $event->isMasterRequest()->shouldBeCalled()->willReturn(true);

        $this->onKernelTerminate($event)->shouldReturn(null);
    }

    function it_should_not_work_when_cart_not_yet_saved(
        OrderInterface $order,
        CartContextInterface $cartContext,
        PostResponseEvent $event
    ): void {
        $order->getId()->shouldBeCalled()->willReturn(null);
        $order->setUpdatedAt(Argument::type(\DateTime::class))->shouldNotBeCalled();
        $cartContext->getCart()->shouldBeCalled();
        $event->isMasterRequest()->shouldBeCalled()->willReturn(true);

        $this->onKernelTerminate($event)->shouldReturn(null);
    }

    function it_should_update_date(
        ObjectManager $orderManager,
        OrderInterface $order,
        CartContextInterface $cartContext,
        PostResponseEvent $event
    ): void {
        $order->getId()->shouldBeCalled()->willReturn(12);
        $order->setUpdatedAt(Argument::type(\DateTime::class))->shouldBeCalled();
        $cartContext->getCart()->shouldBeCalled();
        $event->isMasterRequest()->shouldBeCalled()->willReturn(true);
        $orderManager->persist($order)->shouldBeCalled();
        $orderManager->flush()->shouldBeCalled();

        $this->onKernelTerminate($event)->shouldReturn(null);
    }
}
