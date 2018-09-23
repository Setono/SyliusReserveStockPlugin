<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    public function inCartQuantityForProductVariant(ProductVariantInterface $productVariant): int;
}
