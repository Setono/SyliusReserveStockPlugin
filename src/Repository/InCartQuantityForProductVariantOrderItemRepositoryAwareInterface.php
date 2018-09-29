<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

interface InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    /**
     * Returns the quantity of the injected product variant that are in the carts of all customers, including
     * the current customer.
     *
     * In case there is an order (cart) provided, the quantity of this product variant in that order is
     * discarded. This can be helpful in case you want to check there are items available for the current
     * customer to add (e.g. adding a single item) or whether the full amount (e.g. on cart amount change)
     * is available. In the latter case you would want to have the current customer's cart discarded.
     */
    public function inCartQuantityForProductVariantExcludingOrder(ProductVariantInterface $productVariant, int $ttl, ?OrderInterface $order): int;
}
