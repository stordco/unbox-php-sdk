<?php

include __DIR__ . '/../vendor/autoload.php';

use StordUnbox\Api;
use StordUnbox\Exception\UnboxException;
use StordUnbox\Model\Order;
use StordUnbox\Model\Customer;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Client;

$httpClient = new Client();
$streamFactory = new HttpFactory();
$requestFactory = new HttpFactory();

$apiKey = 'YOUR-API-KEY';
$isTest = true;

$api = new Api($httpClient, $requestFactory, $streamFactory, $apiKey, $isTest);

$order = new Order();
$order
    ->setId('1')
    ->setNumber('#100001')
    ->setCreatedAt(new \DateTime())
    ->setCurrency('GBP')
    ->setTotalAmount(123.45)
    ->setTotalItems(2)
    ->setBillingCity('London')
    ->setBillingCountry('GB')
    ->setBillingPostcode('SE15AB')
    ->setShippingCity('London')
    ->setShippingCountry('GB')
    ->setShippingPostcode('SE15AB')
    ->setGiftMessage('I hope you enjoy the socks, love Mum. xxx')
    ->setGiftMessageRecipient('Tim')
    ->setProductTitles(['Red Socks', 'Blue Socks'])
    ->setPromoCodes(['15OFF_SOCKS'])
    ->setSkus(['SK-R-1', 'SK-B-1'])
    ->setSubscriptionReorder(false)
    ->setTags(['tiktok order'])
    ->setAttributes([
        'attribute1' => 'value1',
        'attribute2' => 'value2',
    ]);

$customer = new Customer();
$customer->setEmail('john.doe@example.com')
    ->setFirstName('John')
    ->setLastName('Doe')
    ->setLanguage('en')
    ->setMarketingConsent(true)
    ->setVendorCustomerId('89714912')
    ->setTags(['VIP', 'Loyal Customer'])
    ->setTotalOrders(5)
    ->setTotalSpent(1234.56)
    ->setAttributes([
        'customer_att1' => 'value1',
        'customer_att2' => 'value2',
    ]);

try {
    $api->sendOrder($order, $customer, 'magento', '1.0.1');
} catch (UnboxException $e) {
    print 'OOPS! Something went wrong: ' . $e->getMessage();
}
