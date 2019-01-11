# SyliusReserveStockPlugin

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

## Installation

### 1. Composer

`composer require setono/sylius-reserve-stock-plugin`

### 2. Load bundle

Add to the bundle list in `config/bundles.php`:

```php
<?php

return [
    // ...
    Setono\SyliusReserveStockPlugin\SetonoSyliusReserveStockPlugin::class => ['all' => true],
    // ...
];
```

### 3. Configuration

Default configuration is applied automatically. Find out which settings can be adjusted by running:

```bash
bin/console config:dump-reference SetonoSyliusReserveStockPlugin
```

The default configuration is:

```yaml
setono_sylius_reserve_stock:

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

namespace App\Repository\OrderItemRepository;

use Setono\SyliusReserveStockPlugin\Repository\InCartQuantityForProductVariantOrderItemRepositoryAwareInterface;
use Setono\SyliusReserveStockPlugin\Repository\ProductVariantCartOrderItem;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderItemRepository as BaseOrderItemRepository;

final class OrderItemRepository extends BaseOrderItemRepository implements InCartQuantityForProductVariantOrderItemRepositoryAwareInterface
{
    use ProductVariantCartOrderItem; // Load trait here
}
```

[ico-version]: https://img.shields.io/packagist/v/setono/sylius-reserve-stock-plugin.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Setono/SyliusReserveStockPlugin/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Setono/SyliusReserveStockPlugin.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/setono/sylius-reserve-stock-plugin
[link-travis]: https://travis-ci.org/Setono/SyliusReserveStockPlugin
[link-code-quality]: https://scrutinizer-ci.com/g/Setono/SyliusReserveStockPlugin
