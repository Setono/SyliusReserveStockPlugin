<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

trait ProductVariantCartOrderItem
{
    /**
     * @see InCartQuantityForProductVariantOrderItemRepositoryAwareInterface::inCartQuantityForProductVariantExcludingOrder()
     */
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

    /**
     * Create the base query without taking in account any order. It looks for order items from orders
     * that are marked as a 'cart' and where the product variant is the same as requested.
     */
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

    /**
     * Takes the query builder, transform it into a query and makes sure in all situations
     * an integer is being returned. In case the query does not result anything, `null` is returned
     * which will be transformed into the integer 0.
     */
    private function produceSingleScalarQueryIntegerResult(QueryBuilder $queryBuilder): int
    {
        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        if (null === $result) {
            return 0;
        }

        return (int) $result;
    }
}
