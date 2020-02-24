# Kraken.io PHP SDK

[![Latest Version][ico-version]][link-packagist]
[![Latest Unstable Version][ico-unstable-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Quality Score][ico-code-quality]][link-code-quality]

A PHP SDK for the [Kraken.io API](https://kraken.io/docs/getting-started).

## Installation

### Download
Open a command console, enter your project directory and execute the following command to download the latest stable version of this library:

```bash
$ composer require setono/kraken-io-php-sdk
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Install HTTP client
This library is HTTP client agnostic so you need to provide your own PSR18 HTTP client implementation.
If you don't want to bother with this, you can just do this:

```bash
$ composer require kriswallsmith/buzz nyholm/psr7
```

This will install the PSR18 HTTP client, Buzz and HTTP message factories used to create requests, responses, and streams.

## Usage

### Upload file via URL and wait for a response
```php
<?php
use Setono\Kraken\Client\Client;
use Setono\Kraken\Client\Response\WaitResponse;

$client = new Client('Your API key', 'Your API secret');

/** @var WaitResponse $response */
$response = $client->url('https://www.your-domain.com/your/image.jpg', true);

/** @var SplFileInfo $optimizedImageFile */
$optimizedImageFile = $response->getFile();
```

### Get user status
```php
<?php
use Setono\Kraken\Client\Client;
use Setono\Kraken\Client\Response\UserStatusResponse;

$client = new Client('Your API key', 'Your API secret');

/** @var UserStatusResponse $response */
$response = $client->status();

echo sprintf('Quota total: %s', $response->getQuotaTotal());
echo sprintf('Quota used: %s', $response->getQuotaUsed());
echo sprintf('Quota remaining: %s', $response->getQuotaRemaining());
```

### I want to use my own HTTP client implementation and message factories

You can inject your own implementations when instantiating the client:

```php
<?php
use Setono\Kraken\Client\Client;

$client = new Client('Your API key', 'Your API secret', $httpClient, $httpRequestFactory, $httpStreamFactory);
```

[ico-version]: https://poser.pugx.org/setono/kraken-io-php-sdk/v/stable
[ico-unstable-version]: https://poser.pugx.org/setono/kraken-io-php-sdk/v/unstable
[ico-license]: https://poser.pugx.org/setono/kraken-io-php-sdk/license
[ico-github-actions]: https://github.com/Setono/kraken-io-php-sdk/workflows/build/badge.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Setono/kraken-io-php-sdk.svg

[link-packagist]: https://packagist.org/packages/setono/kraken-io-php-sdk
[link-github-actions]: https://github.com/Setono/kraken-io-php-sdk/actions
[link-code-quality]: https://scrutinizer-ci.com/g/Setono/kraken-io-php-sdk
