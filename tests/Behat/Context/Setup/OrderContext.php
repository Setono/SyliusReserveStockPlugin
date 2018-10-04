<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
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
     * @var ProductVariantResolverInterface
     */
    private $variantResolver;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        FactoryInterface $orderFactory,
        FactoryInterface $orderItemFactory,
        OrderItemQuantityModifierInterface $itemQuantityModifier,
        FactoryInterface $customerFactory,
        RepositoryInterface $customerRepository,
        ObjectManager $objectManager,
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
        $this->variantResolver = $variantResolver;
    }

    /**
     * @Given :numberOfCustomers customers have added :quantity :product products to the cart
     */
    public function customersHaveAddedProductToTheCart(
        string $numberOfCustomers,
        string $quantity,
        ProductInterface $product
    ) {
        $customers = $this->generateCustomers((int)$numberOfCustomers);
        $variant = $this->variantResolver->getVariant($product);

        for ($i = 0; $i < $numberOfCustomers; ++$i) {
            $order = $this->createCart($customers[random_int(0, $numberOfCustomers - 1)]);

            $this->addVariantToOrder($order, $variant, (int)$quantity, 10000);

            $this->objectManager->persist($order);
        }

        $this->objectManager->flush();
    }

    private function addVariantToOrder(
        OrderInterface $order,
        ProductVariantInterface $variant,
        int $quantity,
        int $price
    ) {
        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setUnitPrice($price);

        $this->itemQuantityModifier->modify($item, $quantity);

        $order->addItem($item);
    }

    private function createCart(
        CustomerInterface $customer,
        ChannelInterface $channel = null,
        ?string $localeCode = null
    ): OrderInterface {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->createNew();

        $order->setCustomer($customer);
        $order->setChannel($channel ?? $this->sharedStorage->get('channel'));
        $order->setLocaleCode($localeCode ?? $this->sharedStorage->get('locale')->getCode());
        $order->setCurrencyCode($order->getChannel()->getBaseCurrency()->getCode());

        return $order;
    }

    /**
     * @return CustomerInterface[]
     */
    private function generateCustomers(int $count): array
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
