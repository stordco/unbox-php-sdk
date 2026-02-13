<?php

namespace Unit\Model;

use StordUnbox\Exception\UnboxException;
use StordUnbox\Model\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testItReturnsAnArrayWithMinimalFieldsSet()
    {
        $createdAt = new \DateTime();

        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');

        $this->assertEquals([
            'id' => '123',
            'number' => '#123',
            'total_amount' => 123.45,
            'total_items' => 1,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'currency' => 'GBP',
        ], $order->toArray());
    }

    public function testItReturnsAnArrayWithAllFieldsSet()
    {
        $createdAt = new \DateTime();

        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');
        $order->setBillingCountry('GB');
        $order->setBillingPostcode('SW1A 1AA');
        $order->setBillingCity('London');
        $order->setShippingCountry('GB');
        $order->setShippingPostcode('SW1A 1AA');
        $order->setShippingCity('London');
        $order->setGiftMessage('Happy Birthday!');
        $order->setGiftMessageRecipient('Tim');
        $order->setSkus(['SKU-123', 'SKU-456']);
        $order->setProductTitles(['Product 1', 'Product 2']);
        $order->setPromoCodes(['PROMO-123', 'PROMO-456']);
        $order->setSubscriptionReorder(true);
        $order->setTags(['tag1', 'tag2']);
        $order->setAttributes([
            'attribute1' => 'value1',
            'attribute2' => 'value2',
            'attribute3' => '',
            'attribute4' => ['list', 'of', 'values']
        ]);

        $this->assertEquals([
            'id' => '123',
            'number' => '#123',
            'total_amount' => 123.45,
            'total_items' => 1,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'currency' => 'GBP',
            'billing_country' => 'GB',
            'billing_postcode' => 'SW1A 1AA',
            'billing_city' => 'London',
            'shipping_country' => 'GB',
            'shipping_postcode' => 'SW1A 1AA',
            'shipping_city' => 'London',
            'gift_message' => 'Happy Birthday!',
            'gift_message_recipient' => 'Tim',
            'skus' => ['SKU-123', 'SKU-456'],
            'product_titles' => ['Product 1', 'Product 2'],
            'promo_codes' => ['PROMO-123', 'PROMO-456'],
            'is_subscription_reorder' => true,
            'tags' => ['tag1', 'tag2'],
            'attributes' => [
                'attribute1' => 'value1',
                'attribute2' => 'value2',
                'attribute3' => '',
                'attribute4' => ['list', 'of', 'values'],
            ]
        ], $order->toArray());
    }

    public function testItReturnsAnArrayIgnoringEmptyFields()
    {
        $createdAt = new \DateTime();

        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');
        // We ignore empty fields - these won't be in the output
        $order->setBillingPostcode('');
        $order->setBillingCountry('');
        $order->setBillingCity('');
        $order->setShippingPostcode('');
        $order->setShippingCountry('');
        $order->setShippingCity('');
        $order->setGiftMessage('');
        $order->setGiftMessageRecipient('');
        $order->setSkus([]);
        $order->setProductTitles([]);
        $order->setPromoCodes([]);
        $order->setTags([]);
        $order->setAttributes([]);

        $this->assertEquals([
            'id' => '123',
            'number' => '#123',
            'total_amount' => 123.45,
            'total_items' => 1,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'currency' => 'GBP',
        ], $order->toArray());
    }

    public function testItThrowsAnExceptionIfRequiredFieldsAreNotSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Required field "number" must be set');

        $order = new Order();
        $order->setId('123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCurrency('GBP');

        $order->toArray();
    }

    public function testItThrowsAnExceptionIfNonStringArrayItemsAreSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('All promo codes array items must be strings');

        $createdAt = new \DateTime();
        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');

        $order->setPromoCodes(['OK', 123, 45.6]);

        $order->toArray();
    }

    public function testItThrowsAnExceptionIfNonStringAttributeKeysAreSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Attribute keys must be strings, received: 1');

        $createdAt = new \DateTime();
        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');
        $order->setAttributes([1 => 'value1']);

        $order->toArray();
    }

    public function testItThrowsAnExceptionIfANullAttributeValueIsSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Received a null value for attribute "my_attribute"');

        $createdAt = new \DateTime();
        $order = new Order();
        $order->setId('123');
        $order->setNumber('#123');
        $order->setTotalAmount(123.45);
        $order->setTotalItems(1);
        $order->setCreatedAt($createdAt);
        $order->setCurrency('GBP');
        $order->setAttributes(['my_attribute' => null]);

        $order->toArray();
    }
}
