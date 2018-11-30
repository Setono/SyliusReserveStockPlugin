<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusReserveStockPlugin\DependencyInjection\SyliusReserveStockExtension;

final class SyliusReserveStockExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new SyliusReserveStockExtension()];
    }

    public function testDefaultTtl()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('setono_sylius_reserve_stock.ttl', 3600);
    }

    public function testOtherTtl()
    {
        $this->load(['ttl' => 800]);

        $this->assertContainerBuilderHasParameter('setono_sylius_reserve_stock.ttl', 800);
    }
}
