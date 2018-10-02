<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;

final class SleepContext implements Context
{
    /**
     * @Then I wait :seconds seconds
     */
    public function waitFor(int $seconds): void
    {
        sleep($seconds);
    }
}
