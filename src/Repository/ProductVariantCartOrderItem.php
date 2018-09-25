<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

trait ProductVariantCartOrderItem
{
    public function inCartQuantityForProductVariantExcludingOrder(
        ProductVariantInterface $productVariant,
        ?OrderInterface $order
    ): int {
        $qb = $this->createOrderItemVariantQueryBuilder($productVariant);

        if (null !== $order && null !== $order->getId()) {
            $qb = $qb
                ->andWhere('o.order != :order')
                ->setParameter('order', $order);
        }

        return $this->produceSingleScalarQueryIntegerResult($qb);
    }

    private function createOrderItemVariantQueryBuilder(ProductVariantInterface $productVariant): QueryBuilder
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->select('SUM(o.quantity) AS quantity')
            ->innerJoin('o.order', 'cart')
            ->andWhere('cart.state = :state')
            ->andWhere('o.variant = :variant')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('variant', $productVariant);
    }

    private function produceSingleScalarQueryIntegerResult(QueryBuilder $queryBuilder): int
    {
        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        if (null === $result) {
            return 0;
        }

        return (int) $result;
    }
}
