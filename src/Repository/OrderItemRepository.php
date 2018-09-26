<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;

final class OrderItemRepository extends BaseOrderItemRepository implements InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    use ProductVariantCartOrderItem;
}
