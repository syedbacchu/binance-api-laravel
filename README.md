# binance-api-laravel | Integrate Binance API with Laravel Effortlessly

[![Latest Version](https://img.shields.io/github/release/syedbacchu/binance-api-laravel.svg?style=flat-square)](https://github.com/syedbacchu/binance-api-laravel/releases)
[![Issues](https://img.shields.io/github/issues/syedbacchu/binance-api-laravel.svg?style=flat-square)](https://github.com/syedbacchu/binance-api-laravel)
[![Stars](https://img.shields.io/github/stars/syedbacchu/binance-api-laravel.svg?style=social)](https://github.com/syedbacchu/binance-api-laravel)
[![Stars](https://img.shields.io/github/forks/syedbacchu/binance-api-laravel?style=flat-square)](https://github.com/syedbacchu/binance-api-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/sdtech/binance-api-laravel.svg?style=flat-square)](https://packagist.org/packages/sdtech/binance-api-laravel)

- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Uses](#Uses)

## About

This offers a comprehensive integration of the Binance API with Laravel, allowing developers to easily incorporate cryptocurrency trading features into their Laravel applications. It includes extensive documentation, sample codes, and best practices for a seamless and secure setup.
The current features are :

- Trading
- Broker Account
- Public Api

## Requirements

* [Laravel 5.8+](https://laravel.com/docs/installation)
* [PHP ^7](https://www.php.net/)

## Installation
1. From your projects root folder in terminal run:

```bash
composer require sdtech/binance-api-laravel
```
2. Publish the packages views, config file, assets, and language files by running the following from your projects root folder:

```bash
php artisan vendor:publish --tag=binanceapilaravel
```

## configuration
1. Go to your config folder, then open "binanceapilaravel.php" file
2. here you must add that info or add the info to your .env file .

```php
'BINANCE_API_LIVE_URL' => env('BINANCE_API_LIVE_URL') ?? "https://api.binance.com/api/",
'BINANCE_API_TESTNET_URL' => env('BINANCE_API_TESTNET_URL') ?? "https://testnet.binance.vision/api/",
'BINANCE_WAPI_URL' => env('BINANCE_WAPI_URL') ?? "https://api.binance.com/wapi/",
'BINANCE_SAPI_URL' => env('BINANCE_SAPI_URL') ?? "https://api.binance.com/sapi/",
'BINANCE_FAPI_URL' => env('BINANCE_FAPI_URL') ?? "https://fapi.binance.com/",
'BINANCE_BAPI_URL' => env('BINANCE_BAPI_URL') ?? "https://www.binance.com/bapi/",
'BINANCE_WSS_STREAM_URL' => env('BINANCE_WSS_STREAM_URL') ?? "wss://stream.binance.com:9443/ws/",
'BINANCE_WSS_STREAM_TESTNET_URL' => env('BINANCE_WSS_STREAM_TESTNET_URL') ?? "wss://testnet.binance.vision/ws/",
'BINANCE_API_KEY' => env('BINANCE_API_KEY'),
'BINANCE_API_SECRET_KEY' => env('BINANCE_API_SECRET_KEY'),
'BINANCE_API_TEST_MODE' => env('BINANCE_API_TEST_MODE')
```

## Uses
5. We provide a sample code of functionality that will help you to integrate easily
