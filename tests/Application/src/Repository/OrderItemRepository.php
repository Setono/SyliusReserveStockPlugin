<?php

declare(strict_types=1);

namespace TestApp\Setono\SyliusReserveStockPlugin\Repository;

use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Setono\SyliusReserveStockPlugin\Repository\ProductVariantCartOrderItem;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;

final class OrderItemRepository extends BaseOrderItemRepository implements InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    use ProductVariantCartOrderItem;
}
