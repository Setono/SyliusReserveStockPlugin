<?php

declare(strict_types=1);

namespace Tests\Loevgaard\SyliusReserveStockPlugin;

use Loevgaard\SyliusReserveStockPlugin\DependencyInjection\SyliusReserveStockExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class SyliusReserveStockExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new SyliusReserveStockExtension()];
    }

    public function testDefaultTtl()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('loevgaard_sylius_reserve_stock_plugin.ttl', 3600);
    }

    public function testOtherTtl()
    {
        $this->load(['ttl' => 800]);

        $this->assertContainerBuilderHasParameter('loevgaard_sylius_reserve_stock_plugin.ttl', 800);
    }
}
