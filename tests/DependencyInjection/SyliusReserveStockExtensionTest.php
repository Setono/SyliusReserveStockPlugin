<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusReserveStockPlugin;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusReserveStockPlugin\DependencyInjection\SetonoSyliusReserveStockExtension;

final class SyliusReserveStockExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [new SetonoSyliusReserveStockExtension()];
    }

    public function testDefaultTtl(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('setono_sylius_reserve_stock.ttl', 3600);
    }

    public function testOtherTtl(): void
    {
        $this->load(['ttl' => 800]);

        $this->assertContainerBuilderHasParameter('setono_sylius_reserve_stock.ttl', 800);
    }
}
