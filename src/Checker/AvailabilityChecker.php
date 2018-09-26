<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Checker;

use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Order\Model\OrderInterface;

final class AvailabilityChecker implements AvailabilityCheckerInterface
{
    /**
     * @var AvailabilityCheckerInterface
     */
    private $availabilityChecker;

    /**
     * @var InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
     */
    private $repository;

    /**
     * @var CartContextInterface
     */
    private $cartContext;

    public function __construct(
        AvailabilityCheckerInterface $availabilityChecker,
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $repository,
        CartContextInterface $cartContext
    ) {
        $this->availabilityChecker = $availabilityChecker;
        $this->repository = $repository;
        $this->cartContext = $cartContext;
    }

    /**
     * {@inheritdoc}
     */
    public function isStockAvailable(StockableInterface $stockable): bool
    {
        /** @var ProductVariantInterface $stockable */
        if (!$stockable instanceof ProductVariantInterface) {
            return $this->availabilityChecker->isStockAvailable($stockable);
        }

        return $this->isStockSufficient($stockable, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function isStockSufficient(StockableInterface $stockable, int $quantity): bool
    {
        /** @var ProductVariantInterface $stockable */
        if (!$stockable instanceof ProductVariantInterface) {
            return $this->availabilityChecker->isStockSufficient($stockable, $quantity);
        }

        if (!$stockable->isTracked()) {
            return true;
        }

        $stockAvailable = $stockable->getOnHand() -
            $stockable->getOnHold() -
            $this->repository->inCartQuantityForProductVariantExcludingOrder($stockable, $this->getCart());

        return $quantity <= $stockAvailable;
    }

    /**
     * Tries to fetch the current cart and return it in that case. If not, null will be returned.
     *
     * A try/catch block is used because the underlying cart context throws an exception in case there is
     * no cart found and a cart will not be generated. In all situations where no cart can be found, we should
     * be sure `null` is returned.
     */
    private function getCart(): ?OrderInterface
    {
        try {
            return $this->cartContext->getCart();
        } catch (CartNotFoundException $e) {
        }

        return null;
    }
}
