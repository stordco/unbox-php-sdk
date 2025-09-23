<?php

include __DIR__ . '/../vendor/autoload.php';

use StordUnbox\Api;
use StordUnbox\Exception\UnboxException;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Client;

$httpClient = new Client();
$streamFactory = new HttpFactory();
$requestFactory = new HttpFactory();

$apiKey = 'YOUR-API-KEY';
$isTest = true;

// This example sets the origin app version on the API class
// Alternatively, it could be set on the installStore() call
$api = new Api($httpClient, $requestFactory, $streamFactory, $apiKey, $isTest, '1.0.1');

try {
    $api->installStore('store.example.com');
} catch (UnboxException $e) {
    print 'OOPS! Something went wrong: ' . $e->getMessage();
}
