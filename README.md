# Stord Unbox PHP SDK

This client library introduces a reusable API interface for PHP applications to communicate with the Stord Unbox platform.
The SDK wraps up methods to simplify making requests, authorization, error handling and provides guidance of the available parameters via models.
It follows the PSR-7, PSR-17 and PSR-18 standards, relying on proven external libraries to provide the HTTP client and message factories.

For development, we have bundled in Guzzle, but you are free to choose your own, or use the client included with your platform, as long as it supports these standards.

See the [Unbox API documentation](https://pennyblack.stoplight.io/docs/pennyblack/) for full details of the available end-points.

## Prerequisites

- PHP >=7.4
- Composer

## Installation

For production environments you can include the library as a dependency in your project using composer.

```bash
composer require pennyblack/php-sdk
```

You will also need to ensure you have packages that satisfy the virtual `psr/http-client-implementation` and `psr/http-factory-implementation` requirements.
If you do not, then you can require Guzzle, which will satisfy both:

```bash
composer require guzzlehttp/guzzle
```

## Usage

See the [example](example) folder for working examples of how to use the library.

### Creating an API instance

You will need an API key to access Unbox services. If you have a test environment setup then you can set the `$isTest` flag to true to make requests against our test servers. For most customers we only offer production accounts.

The example below uses Guzzle, but you can use any PSR-18 compatible HTTP client, see the package options [here](https://packagist.org/providers/psr/http-client-implementation) and [here](https://packagist.org/providers/psr/http-factory-implementation).

```php
<?php

include __DIR__ . "/../vendor/autoload.php";

use StordUnbox\Api;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Client;

$httpClient = new Client();
$streamFactory = new HttpFactory();
$requestFactory = new HttpFactory();

$apiKey = "YOUR-API-KEY";
$myIntegrationVersion = "1.0.1";
$isTest = true;

$api = new Api($httpClient, $requestFactory, $streamFactory, $apiKey, $isTest, $myIntegrationVersion);
```

The `$myIntegrationVersion` parameter is used to identify which version of the integration is being run to our support team.
If you are using a custom integration then this is of limited value, but for building platform modules/plugins/extensions it can be very useful.
This parameter can either be passed into the API constructor, or set on specific function calls that support it, whichever approach is most convenient for your integration.

### Installing your store

This request acts as validation for your API key and configures Unbox with your store domain:

```php
$api->installStore("your.store.com", "1.0.1");
```

The second parameter is the version of your integration and is optional, see the note above.

### Sending an order

You should send all created orders to our orders ingest endpoint:

```php
$order = new Order()
    ->setId("42")
    ->setNumber("#42")
    ->setCreatedAt(new DateTime())
    ->setCurrency('GBP')
    ->setTotalAmount(123.45)
    ->setTotalItems(2)
    ;

// You only need to send optional fields if you have data for them
if ($hasShippingAddress) {
    $order->setShippingCity('London')
    ->setShippingCountry('GB')
    ->setShippingPostcode('SE15AB')
    ;
}

$customer = new Customer()
    ->setFirstName("John")
    ->setLastName("Doe")
    ->setEmail("john@example.com"
    ->setVendorCustomerId("42")
    ...
    ;

$origin = "magento";

$api->sendOrder($order, $customer, $origin, "1.0.1");
```

The last parameter is the version of your integration and is optional, see the note above.

### Print order

NOTE: Fulfilment endpoints use a separate API Key. For smaller merchants who do their own fulfilment this should be
the same as your merchant API key, but this is not guaranteed. If you are unsure, please contact support.

```php
try {
    $response = $api->requestPrint($orderId, $locationId, $merchantId);
    print($response);
} catch (UnboxException $e) {
    print $e->getMessage();
}

```

### Batch print

```php
try {
    $response = $api->requestBatchPrint($orderIds, $locationId, $merchantId);
    print_r($response);
} catch (UnboxException $e) {
    print $e->getMessage();
}
```

### Get order print status

```php
try {
    $response = $api->getOrderPrintStatus($merchantId, $orderId);
    print_r($response);
} catch (UnboxException $e) {
    print $e->getMessage();
}
```

## Development

`composer install` in development will include dev dependencies to allow you to work with Guzzle and test requests.

See the `example` folder for working examples of how to use the library.

### Tests & Linting

We use PHPUnit for unit testing the app and PHPStan, PHP CodeSniffer and PHP Mess Detector for quality checks.

- Run `composer unit-test` to run the unit tests.
- Run `composer quality-check` to run the quality check tools.
