<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

interface InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    public function inCartQuantityForProductVariant(ProductVariantInterface $productVariant): int;

    public function inCartQuantityForProductVariantExcludingOrder(ProductVariantInterface $productVariant, ?OrderInterface $order): int;
}
