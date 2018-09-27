<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

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
     * @Given :numberOfCustomers customers have added :quantity :product products to the cart
     */
    public function customersHaveAddedProductToTheCart(
        $numberOfCustomers,
        $quantity,
        ProductInterface $product
    ) {
        $customers = $this->generateCustomers($numberOfCustomers);
        $variant = $this->variantResolver->getVariant($product);

        for ($i = 0; $i < $numberOfCustomers; ++$i) {
            $order = $this->createCart($customers[random_int(0, $numberOfCustomers - 1)]);

            $this->addVariantToOrder($order, $variant, (int)$quantity, 10000);

            $this->objectManager->persist($order);
        }

        $this->objectManager->flush();
    }

    private function addVariantToOrder(OrderInterface $order, ProductVariantInterface $variant, int $quantity, $price)
    {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($price);

        $this->itemQuantityModifier->modify($item, $quantity);

        $order->addItem($item);
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
            $customer->setLastname('Doe'.$i);

            $customers[] = $customer;

            $this->customerRepository->add($customer);
        }

        return $customers;
    }
}
