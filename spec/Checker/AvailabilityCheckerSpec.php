<?php

declare(strict_types=1);

namespace spec\Setono\SyliusReserveStockPlugin\Checker;

use PhpSpec\ObjectBehavior;
use Setono\SyliusReserveStockPlugin\Checker\AvailabilityChecker;
use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityChecker as DecoratedAvailabilityChecker;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Order\Context\CartContextInterface;

final class AvailabilityCheckerSpec extends ObjectBehavior
{
    function let(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        CartContextInterface $cartContext,
        OrderInterface $order
    ): void {
        $cartContext->getCart()->willReturn($order);

        $this->beConstructedWith(
            new DecoratedAvailabilityChecker(),
            $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
            $cartContext
        );
    }

    function it_is_an_inventory_availability_checker(): void
    {
        $this->shouldImplement(AvailabilityCheckerInterface::class);
    }

    function we_test_the_expected_plugin_class(): void
    {
        $this->shouldBeAnInstanceOf(AvailabilityChecker::class);
    }

    function it_recognizes_stockable_as_available_if_on_hand_quantity_is_greater_than_0_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(5);
        $stockable->getOnHold()->willReturn(0);

        $this->isStockAvailable($stockable)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_not_available_if_on_hand_quantity_is_equal_to_0_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(0);
        $stockable->getOnHold()->willReturn(0);

        $this->isStockAvailable($stockable)->shouldReturn(false);
    }

    function it_recognizes_stockable_as_available_if_on_hold_quantity_is_less_than_on_hand_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(5);
        $stockable->getOnHold()->willReturn(4);

        $this->isStockAvailable($stockable)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_not_available_if_on_hold_quantity_is_same_as_on_hand_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(5);
        $stockable->getOnHold()->willReturn(5);

        $this->isStockAvailable($stockable)->shouldReturn(false);
    }

    function it_recognizes_stockable_as_sufficient_if_on_hand_minus_on_hold_quantity_is_greater_than_the_required_quantity_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(10);
        $stockable->getOnHold()->willReturn(3);

        $this->isStockSufficient($stockable, 5)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_sufficient_if_on_hand_minus_on_hold_quantity_is_equal_to_the_required_quantity_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(true);
        $stockable->getOnHand()->willReturn(10);
        $stockable->getOnHold()->willReturn(5);

        $this->isStockSufficient($stockable, 5)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_available_or_sufficent_if_it_is_not_tracked_via_decorated_service(
        StockableInterface $stockable
    ): void {
        $stockable->isTracked()->willReturn(false);

        $this->isStockAvailable($stockable)->shouldReturn(true);
        $this->isStockSufficient($stockable, 42)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_available_if_on_hand_quantity_is_greater_than_0(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(0);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(5);
        $productVariant->getOnHold()->willReturn(0);

        $this->isStockAvailable($productVariant)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_not_available_if_on_hand_quantity_is_equal_to_0(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(0);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(0);
        $productVariant->getOnHold()->willReturn(0);

        $this->isStockAvailable($productVariant)->shouldReturn(false);
    }

    function it_recognizes_stockable_as_available_if_on_hold_quantity_is_less_than_on_hand_and_stock_reserved(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(1);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(5);
        $productVariant->getOnHold()->willReturn(3);

        $this->isStockAvailable($productVariant)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_not_available_if_on_hold_quantity_is_same_as_on_hand_and_stock_reserved(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(2);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(5);
        $productVariant->getOnHold()->willReturn(3);

        $this->isStockAvailable($productVariant)->shouldReturn(false);
    }

    function it_recognizes_stockable_as_sufficient_if_on_hand_minus_on_hold_quantity_is_greater_than_the_required_quantity_and_stock_reserved(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(2);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(10);
        $productVariant->getOnHold()->willReturn(3);

        $this->isStockSufficient($productVariant, 5)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_sufficient_if_on_hand_minus_on_hold_quantity_is_equal_to_the_required_quantity(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(0);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(10);
        $productVariant->getOnHold()->willReturn(5);

        $this->isStockSufficient($productVariant, 5)->shouldReturn(true);
    }

    function it_recognizes_stockable_as_insufficient_if_stock_is_all_reserved(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(5);

        $productVariant->isTracked()->willReturn(true);
        $productVariant->getOnHand()->willReturn(10);
        $productVariant->getOnHold()->willReturn(5);

        $this->isStockSufficient($productVariant, 1)->shouldReturn(false);
    }

    function it_recognizes_stockable_as_available_or_sufficent_if_it_is_not_tracked(
        InCartQuantityForProductVariantOrderItemRepositoryAwareInterface $inCartQuantityForProductVariantExcludingOrderOrderItemRepository,
        ProductVariantInterface $productVariant,
        OrderInterface $order
    ): void {
        $inCartQuantityForProductVariantExcludingOrderOrderItemRepository->inCartQuantityForProductVariantExcludingOrder(
            $productVariant,
            $order
        )->willReturn(2);

        $productVariant->isTracked()->willReturn(false);

        $this->isStockAvailable($productVariant)->shouldReturn(true);
        $this->isStockSufficient($productVariant, 42)->shouldReturn(true);
    }
}
