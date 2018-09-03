# SyliusReserveStockPlugin

[![License](https://img.shields.io/packagist/l/loevgaard/SyliusReserveStockPlugin.svg)](https://packagist.org/packages/loevgaard/SyliusReserveStockPlugin)
[![Version](https://img.shields.io/packagist/v/loevgaard/SyliusReserveStockPlugin.svg)](https://packagist.org/packages/loevgaard/SyliusReserveStockPlugin)
[![Build status on Linux](https://img.shields.io/travis/loevgaard/SyliusReserveStockPlugin/master.svg)](http://travis-ci.org/loevgaard/SyliusReserveStockPlugin)

## Installation

### 1. Composer

`composer require loevgaard/sylius-reserve-stock-plugin`

### 2. Load bundle

Add to the bundle list in `app/AppKernel.php`:

```php
new \Loevgaard\SyliusReserveStockPlugin\SyliusReserveStockPlugin(),
```

### 3. Configuration

Default configuration is applied automatically. Find out which settings can be adjusted by running:

```bash
bin/console config:dump-reference SyliusReserveStockPlugin
```
