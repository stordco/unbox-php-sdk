<?php

include __DIR__ . '/../vendor/autoload.php';

use StordUnbox\Exception\UnboxException;
use StordUnbox\Api;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Client;

$httpClient = new Client();
$streamFactory = new HttpFactory();
$requestFactory = new HttpFactory();

$apiKey = 'YOUR-API-KEY';
$isTest = true;

$api = new Api($httpClient, $requestFactory, $streamFactory, $apiKey, $isTest);

// This can be either the internal ecommerce platform order id or the customer-friendly order number
$orderId = 'YOUR-ORDER-ID';

// If integrating for a 3PL / Warehouse with multiple vendors you will need the merchant_id
// If you are the merchant fulfilling your own orders then this should not be necessary
//$merchantId = null;
$merchantId = 'YOUR-MERCHANT-ID';

// If you have a single printer then you do not need a print location
// If you have multiple printers setup then you need to send the location you want to print from
//$locationId = null;
$locationId = 'YOUR-LOCATION-ID';

try {
    $response = $api->requestPrint($orderId, $locationId, $merchantId);
    print($response);
} catch (UnboxException $e) {
    print 'OOPS! Something went wrong: ' . $e->getMessage();
}
