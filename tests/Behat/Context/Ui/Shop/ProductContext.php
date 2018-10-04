<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Product\ShowPageInterface;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
{
    /**
     * @var ShowPageInterface
     */
    private $showPage;

    public function __construct(ShowPageInterface $showPage)
    {
        $this->showPage = $showPage;
    }

    /**
     * @Then I should see that it is in stock
     */
    public function iShouldSeeItIsInStock()
    {
        Assert::false($this->showPage->isOutOfStock());
    }

    /**
     * @Then I should be able to add it to the cart
     */
    public function iShouldBeableToAddItToTheCart()
    {
        Assert::true($this->showPage->hasAddToCartButton());
    }
}
