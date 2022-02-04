<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Listener;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

final class UpdateCartOnPageviewListener
{
    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var ObjectManager
     */
    private $orderManager;

    public function __construct(
        CartContextInterface $cartContext,
        ObjectManager $orderManager
    ) {
        $this->cartContext = $cartContext;
        $this->orderManager = $orderManager;
    }

    public function onKernelTerminate(PostResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        try {
            $cart = $this->cartContext->getCart();
        } catch (CartNotFoundException $e) {
            return;
        }

        if (null === $cart->getId()) {
            return;
        }

        $cart->setUpdatedAt(new \DateTime());

        $this->orderManager->persist($cart);
        $this->orderManager->flush();
    }
}
