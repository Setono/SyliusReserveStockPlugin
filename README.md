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
