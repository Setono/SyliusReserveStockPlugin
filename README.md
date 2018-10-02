# SyliusReserveStockPlugin

[![License](https://img.shields.io/packagist/l/setono/SyliusReserveStockPlugin.svg)](https://packagist.org/packages/setono/SyliusReserveStockPlugin)
[![Version](https://img.shields.io/packagist/v/setono/SyliusReserveStockPlugin.svg)](https://packagist.org/packages/setono/SyliusReserveStockPlugin)
[![Build status on Linux](https://img.shields.io/travis/setono/SyliusReserveStockPlugin/master.svg)](http://travis-ci.org/setono/SyliusReserveStockPlugin)

## Installation

### 1. Composer

`composer require setono/sylius-reserve-stock-plugin`

### 2. Load bundle

Add to the bundle list in `app/AppKernel.php`:

```php
new \Setono\SyliusReserveStockPlugin\SyliusReserveStockPlugin(),
```

### 3. Configuration

Default configuration is applied automatically. Find out which settings can be adjusted by running:

```bash
bin/console config:dump-reference SyliusReserveStockPlugin
```

The default configuration is:

```yaml
sylius_reserve_stock:

    # Define the Time To Live (TTL) for a product reservation.
    ttl:                  3600 # Example: 1800
```

### 4. Include repository

#### Option 1: load repository via config

This option applies if you didn't extend the `OrderItem` repository yet.

```yaml
sylius_order:
    resources:
        order_item:
            classes:
                repository: Setono\SyliusReserveStockPlugin\Repository\OrderItemRepository
```

#### Option 2: include repository trait in your repository

This option applies if you extended the `OrderItem` repository already. Add the trait to your repository class as shown in the
example below. The package also comes with an interface (`InCartQuantityForProductVariantOrderItemRepositoryAwareInterface`) which you can
optionally load.

```php
<?php

declare(strict_types=1);

namespace AppBundle\Repository\OrderItemRepository;

use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Setono\SyliusReserveStockPlugin\Repository\ProductVariantCartOrderItem;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;

final class OrderItemRepository extends BaseOrderItemRepository implements InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    use ProductVariantCartOrderItem; // Load trait here
}
```
