<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\PromotionCouponInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Shipping\ShipmentTransitions;

final class OrderContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var FactoryInterface
     */
    private $orderFactory;

    /**
     * @var FactoryInterface
     */
    private $orderItemFactory;

    /**
     * @var OrderItemQuantityModifierInterface
     */
    private $itemQuantityModifier;

    /**
     * @var FactoryInterface
     */
    private $customerFactory;

    /**
     * @var RepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StateMachineFactoryInterface
     */
    private $stateMachineFactory;

    /**
     * @var ProductVariantResolverInterface
     */
    private $variantResolver;

    /**
     * @param SharedStorageInterface $sharedStorage
     * @param OrderRepositoryInterface $orderRepository
     * @param FactoryInterface $orderFactory
     * @param FactoryInterface $orderItemFactory
     * @param OrderItemQuantityModifierInterface $itemQuantityModifier
     * @param FactoryInterface $customerFactory
     * @param RepositoryInterface $customerRepository
     * @param ObjectManager $objectManager
     * @param StateMachineFactoryInterface $stateMachineFactory
     * @param ProductVariantResolverInterface $variantResolver
     */
    public function __construct(
        SharedStorageInterface $sharedStorage,
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $orderFactory,
        FactoryInterface $orderItemFactory,
        OrderItemQuantityModifierInterface $itemQuantityModifier,
        FactoryInterface $customerFactory,
        RepositoryInterface $customerRepository,
        ObjectManager $objectManager,
        StateMachineFactoryInterface $stateMachineFactory,
        ProductVariantResolverInterface $variantResolver
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->itemQuantityModifier = $itemQuantityModifier;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->objectManager = $objectManager;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->variantResolver = $variantResolver;
    }

    /**
     * @Given :numberOfCustomers customers have added (\d+) ("[^"]+" product) to the cart
     */
    public function customersHaveAddedProductsToTheCartForTotalOf($numberOfCustomers, $quantity, ProductInterface $product)
    {
        $customers = $this->generateCustomers($numberOfCustomers);
        $variant = $this->variantResolver->getVariant($product);

        for ($i = 0; $i < $numberOfCustomers; ++$i) {
            $order = $this->createCart($customers[random_int(0, $numberOfCustomers - 1)]);

            $this->addVariantToOrder($order, $sampleProductVariant, 10000);

            $this->objectManager->persist($order);
        }

        $this->objectManager->flush();
    }

    /**
     * @param OrderInterface $order
     * @param ProductVariantInterface $variant
     * @param int $price
     */
    private function addVariantToOrder(OrderInterface $order, ProductVariantInterface $variant, $price)
    {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($price);

        $this->itemQuantityModifier->modify($item, 1);

        $order->addItem($item);
    }

    /**
     * @param OrderInterface $order
     * @param string $transition
     */
    private function applyShipmentTransitionOnOrder(OrderInterface $order, $transition)
    {
        foreach ($order->getShipments() as $shipment) {
            $this->stateMachineFactory->get($shipment, ShipmentTransitions::GRAPH)->apply($transition);
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $transition
     */
    private function applyPaymentTransitionOnOrder(OrderInterface $order, $transition)
    {
        foreach ($order->getPayments() as $payment) {
            $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply($transition);
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $transition
     */
    private function applyTransitionOnOrderCheckout(OrderInterface $order, $transition)
    {
        $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH)->apply($transition);
    }

    /**
     * @param OrderInterface $order
     * @param string $transition
     */
    private function applyTransitionOnOrder(OrderInterface $order, string $transition): void
    {
        $this->stateMachineFactory->get($order, OrderTransitions::GRAPH)->apply($transition);
    }

    /**
     * @param ProductVariantInterface $productVariant
     * @param int $quantity
     *
     * @return OrderInterface
     */
    private function addProductVariantToOrder(ProductVariantInterface $productVariant, $quantity = 1)
    {
        $order = $this->sharedStorage->get('order');

        $this->addProductVariantsToOrderWithChannelPrice(
            $order,
            $this->sharedStorage->get('channel'),
            $productVariant,
            (int) $quantity
        );

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @param ChannelInterface $channel
     * @param ProductVariantInterface $productVariant
     * @param int $quantity
     */
    private function addProductVariantsToOrderWithChannelPrice(
        OrderInterface $order,
        ChannelInterface $channel,
        ProductVariantInterface $productVariant,
        int $quantity = 1
    ) {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($productVariant);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $productVariant->getChannelPricingForChannel($channel);
        $item->setUnitPrice($channelPricing->getPrice());

        $this->itemQuantityModifier->modify($item, $quantity);

        $order->addItem($item);
    }

    /**
     * @param CustomerInterface $customer
     * @param string $number
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return OrderInterface
     */
    private function createOrder(
        CustomerInterface $customer,
        $number = null,
        ChannelInterface $channel = null,
        $localeCode = null
    ) {
        $order = $this->createCart($customer, $channel, $localeCode);

        if (null !== $number) {
            $order->setNumber($number);
        }

        $order->completeCheckout();

        return $order;
    }

    /**
     * @param CustomerInterface $customer
     * @param ChannelInterface|null $channel
     * @param string|null $localeCode
     *
     * @return OrderInterface
     */
    private function createCart(
        CustomerInterface $customer,
        ChannelInterface $channel = null,
        $localeCode = null
    ) {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->createNew();

        $order->setCustomer($customer);
        $order->setChannel($channel ?? $this->sharedStorage->get('channel'));
        $order->setLocaleCode($localeCode ?? $this->sharedStorage->get('locale')->getCode());
        $order->setCurrencyCode($order->getChannel()->getBaseCurrency()->getCode());

        return $order;
    }

    /**
     * @param int $count
     *
     * @return CustomerInterface[]
     */
    private function generateCustomers($count)
    {
        $customers = [];

        for ($i = 0; $i < $count; ++$i) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
            $customer->setEmail(sprintf('john%s@doe.com', uniqid()));
            $customer->setFirstname('John');
            $customer->setLastname('Doe' . $i);

            $customers[] = $customer;

            $this->customerRepository->add($customer);
        }

        return $customers;
    }

    private function getPriceFromString(string $price): int
    {
        return (int) round((float) str_replace(['€', '£', '$'], '', $price) * 100, 2);
    }

    /**
     * @param OrderInterface $order
     * @param ShippingMethodInterface $shippingMethod
     * @param AddressInterface $address
     * @param PaymentMethodInterface $paymentMethod
     */
    private function checkoutUsing(
        OrderInterface $order,
        ShippingMethodInterface $shippingMethod,
        AddressInterface $address,
        PaymentMethodInterface $paymentMethod
    ) {
        $order->setShippingAddress($address);
        $order->setBillingAddress(clone $address);

        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_ADDRESS);

        $this->proceedSelectingShippingAndPaymentMethod($order, $shippingMethod, $paymentMethod);
    }

    /**
     * @param OrderInterface $order
     * @param ShippingMethodInterface $shippingMethod
     * @param PaymentMethodInterface $paymentMethod
     */
    private function proceedSelectingShippingAndPaymentMethod(OrderInterface $order, ShippingMethodInterface $shippingMethod, PaymentMethodInterface $paymentMethod)
    {
        foreach ($order->getShipments() as $shipment) {
            $shipment->setMethod($shippingMethod);
        }
        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);

        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        $payment->setMethod($paymentMethod);

        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
        $this->applyTransitionOnOrderCheckout($order, OrderCheckoutTransitions::TRANSITION_COMPLETE);
    }

    /**
     * @param OrderInterface $order
     * @param ProductVariantInterface $variant
     * @param int $price
     */
    private function addVariantWithPriceToOrder(OrderInterface $order, ProductVariantInterface $variant, $price)
    {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($price);

        $this->itemQuantityModifier->modify($item, 1);

        $order->addItem($item);
    }

    /**
     * @param int $numberOfCustomers
     * @param int $numberOfOrders
     * @param string $total
     * @param bool $isFulfilled
     */
    private function createOrders(
        int $numberOfCustomers,
        int $numberOfOrders,
        string $total,
        bool $isFulfilled = false
    ): void {
        $customers = $this->generateCustomers($numberOfCustomers);
        $sampleProductVariant = $this->sharedStorage->get('variant');
        $total = $this->getPriceFromString($total);

        for ($i = 0; $i < $numberOfOrders; ++$i) {
            $order = $this->createOrder($customers[random_int(0, $numberOfCustomers - 1)], '#' . uniqid());
            $order->setState(OrderInterface::STATE_NEW); // Temporary, we should use checkout to place these orders.
            $this->applyPaymentTransitionOnOrder($order, PaymentTransitions::TRANSITION_COMPLETE);

            $price = $i === ($numberOfOrders - 1) ? $total : random_int(1, $total);
            $total -= $price;

            $this->addVariantWithPriceToOrder($order, $sampleProductVariant, $price);

            if ($isFulfilled) {
                $this->applyTransitionOnOrder($order, OrderTransitions::TRANSITION_FULFILL);
            }

            $this->objectManager->persist($order);
            $this->sharedStorage->set('order', $order);
        }

        $this->objectManager->flush();
    }

    /**
     * @param int $numberOfCustomers
     * @param int $numberOfOrders
     * @param string $total
     * @param ProductInterface $product
     * @param bool $isFulfilled
     */
    private function createOrdersWithProduct(
        int $numberOfCustomers,
        int $numberOfOrders,
        string $total,
        ProductInterface $product,
        bool $isFulfilled = false
    ): void {
        $customers = $this->generateCustomers($numberOfCustomers);
        $sampleProductVariant = $product->getVariants()->first();
        $total = $this->getPriceFromString($total);

        for ($i = 0; $i < $numberOfOrders; ++$i) {
            $order = $this->createOrder($customers[random_int(0, $numberOfCustomers - 1)], '#' . uniqid(), $product->getChannels()->first());
            $order->setState(OrderInterface::STATE_NEW);
            $this->applyPaymentTransitionOnOrder($order, PaymentTransitions::TRANSITION_COMPLETE);

            $price = $i === ($numberOfOrders - 1) ? $total : random_int(1, $total);
            $total -= $price;

            $this->addVariantWithPriceToOrder($order, $sampleProductVariant, $price);

            if ($isFulfilled) {
                $this->applyTransitionOnOrder($order, OrderTransitions::TRANSITION_FULFILL);
            }

            $this->objectManager->persist($order);
        }

        $this->objectManager->flush();
    }

    /**
     * @param CustomerInterface $customer
     * @param int $orderCount
     * @param ChannelInterface $channel
     * @param int $productCount
     * @param ProductInterface $product
     * @param bool $isFulfilled
     */
    private function createOrdersForCustomer(
        CustomerInterface $customer,
        int $orderCount,
        ChannelInterface $channel,
        int $productCount,
        ProductInterface $product,
        bool $isFulfilled = false
    ): void {
        for ($i = 0; $i < $orderCount; ++$i) {
            $order = $this->createOrder($customer, uniqid('#'), $channel);

            $this->addProductVariantsToOrderWithChannelPrice(
                $order,
                $channel,
                $this->variantResolver->getVariant($product),
                (int) $productCount
            );

            $order->setState($isFulfilled ? OrderInterface::STATE_FULFILLED : OrderInterface::STATE_NEW);

            $this->objectManager->persist($order);
        }

        $this->objectManager->flush();
    }

    /**
     * @param ProductInterface $product
     * @param ShippingMethodInterface $shippingMethod
     * @param AddressInterface $address
     * @param PaymentMethodInterface $paymentMethod
     * @param CustomerInterface $customer
     * @param int $number
     */
    private function placeOrder(
        ProductInterface $product,
        ShippingMethodInterface $shippingMethod,
        AddressInterface $address,
        PaymentMethodInterface $paymentMethod,
        CustomerInterface $customer,
        int $number
    ): void {
        /** @var ProductVariantInterface $variant */
        $variant = $this->variantResolver->getVariant($product);

        /** @var ChannelPricingInterface $channelPricing */
        $channelPricing = $variant->getChannelPricingForChannel($this->sharedStorage->get('channel'));

        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($channelPricing->getPrice());

        $this->itemQuantityModifier->modify($item, 1);

        $order = $this->createOrder($customer, '#00000' . $number);
        $order->addItem($item);

        $this->checkoutUsing($order, $shippingMethod, clone $address, $paymentMethod);
        $this->applyPaymentTransitionOnOrder($order, PaymentTransitions::TRANSITION_COMPLETE);

        $this->objectManager->persist($order);
        $this->sharedStorage->set('order', $order);
    }
}
