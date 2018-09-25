<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository\Stubs;

use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Setono\SyliusReserveStockPlugin\Repository\ProductVariantCartOrderItem;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository;

final class ProductVariantCartOrderItemStub extends OrderItemRepository implements InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    use ProductVariantCartOrderItem;
}
