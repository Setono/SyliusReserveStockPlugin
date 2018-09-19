<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Component\Product\Model\ProductInterface;

final class CartContext implements Context
{
    /**
     * @Given /^There are (\d+) unit(?:|s) of (product "([^"]+)") reserved by other visitors$/
     */
    public function productsReservedByVisitors($quantity, ProductInterface $product)
    {
        // @todo @stefandoorn Create another cart with same product (variant) with low TTL
        throw new \Exception('Implement');
    }
}
