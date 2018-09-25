<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

trait ProductVariantCartOrderItem
{
    public function inCartQuantityForProductVariant(ProductVariantInterface $productVariant): int
    {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('o');

        $query = $qb
            ->select('SUM(o.quantity) AS quantity')
            ->innerJoin('o.order', 'cart')
            ->andWhere('cart.state = :state')
            ->andWhere('o.variant = :variant')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('variant', $productVariant)
            ->getQuery();

        $result = $query->getSingleScalarResult();

        if (null === $result) {
            return 0;
        }

        return (int) $result;
    }

    public function inCartQuantityForProductVariantExcludingOrder(
        ProductVariantInterface $productVariant,
        ?OrderInterface $order
    ): int {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('o');

        $qb = $qb
            ->select('SUM(o.quantity) AS quantity')
            ->innerJoin('o.order', 'cart')
            ->andWhere('cart.state = :state')
            ->andWhere('o.variant = :variant')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('variant', $productVariant);

        if (null !== $order && null !== $order->getId()) {
            $qb = $qb
                ->andWhere('o.order != :order')
                ->setParameter('order', $order);
        }

        $result = $qb->getQuery()->getSingleScalarResult();

        if (null === $result) {
            return 0;
        }

        return (int) $result;
    }
}
