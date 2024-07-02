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
- Sub Account

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
3.
 ``` bash
    'TEST' => env('TEST') ?? "",
   ```

## Uses
1. We provide a sample code of functionality that will help you to integrate easily

