<?php

declare(strict_types=1);

namespace spec\Setono\SyliusReserveStockPlugin\Repository;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderInterface;

class ProductVariantCartOrderItemSpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant)
    {
        $classMetadata->name = 'order';

        $this->beAnInstanceOf('Setono\SyliusReserveStockPlugin\Repository\OrderItemRepository');
        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(InCartQuantityForProductVariantOrderItemRepositoryAwareInterface::class);
    }

    function it_should_not_use_the_order_if_empty_but_has_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant)
    {
        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.updatedAt > :expireDate')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('expireDate', Argument::type(DateTime::class))->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(8);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 3600, null)->shouldReturn(8);
    }

    function it_should_not_use_the_order_if_empty_and_has_no_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant)
    {
        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.updatedAt > :expireDate')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('expireDate', Argument::type(DateTime::class))->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(null);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 3600, null)->shouldReturn(0);
    }

    function it_should_use_the_order_if_empty_but_has_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant, OrderInterface $order)
    {
        $order->getId()->shouldBeCalled()->willReturn(12);

        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.order != :order')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('order', $order)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.updatedAt > :expireDate')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('expireDate', Argument::type(DateTime::class))->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(8);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 3600, $order)->shouldReturn(8);
    }

    function it_should_use_the_order_if_empty_and_has_no_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant, OrderInterface $order)
    {
        $order->getId()->shouldBeCalled()->willReturn(12);

        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.order != :order')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('order', $order)->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.updatedAt > :expireDate')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('expireDate', Argument::type(DateTime::class))->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(null);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 3600, $order)->shouldReturn(0);
    }

    function it_should_not_use_the_ttl_if_below_1_but_has_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant)
    {
        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(8);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 0, null)->shouldReturn(8);
    }

    function it_should_not_use_the_ttl_if_below_1_and_has_no_results($em, QueryBuilder $builder, AbstractQuery $query, ProductVariantInterface $productVariant)
    {
        $em->createQueryBuilder()->shouldBeCalled()->willReturn($builder);
        $builder->select('o')->shouldBeCalled()->willReturn($builder);
        $builder->from('order', 'o', null)->shouldBeCalled()->willReturn($builder);
        $builder->select('SUM(o.quantity) AS quantity')->shouldBeCalled()->willReturn($builder);
        $builder->innerJoin('o.order', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('cart.state = :state')->shouldBeCalled()->willReturn($builder);
        $builder->andWhere('o.variant = :variant')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('state', 'cart')->shouldBeCalled()->willReturn($builder);
        $builder->setParameter('variant', $productVariant)->shouldBeCalled()->willReturn($builder);

        $builder->getQuery()->shouldBeCalled()->willReturn($query);
        $query->getSingleScalarResult()->shouldBeCalled()->willReturn(null);

        $this->inCartQuantityForProductVariantExcludingOrder($productVariant, 0, null)->shouldReturn(0);
    }
}
