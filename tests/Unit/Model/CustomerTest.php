<?php

namespace Unit\Model;

use StordUnbox\Exception\UnboxException;
use StordUnbox\Model\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testItReturnsAnArrayWithMinimalFieldsSet()
    {
        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail('john@example.com');

        $this->assertEquals([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ], $customer->toArray());
    }

    public function testItReturnsAnArrayWithAllFieldsSet()
    {
        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail('john@example.com');
        $customer->setVendorCustomerId('123');
        $customer->setLanguage('en');
        $customer->setMarketingConsent(true);
        $customer->setTotalOrders(5);
        $customer->setTags(['VIP', 'Loyal Customer']);
        $customer->setTotalSpent(1234.56);
        $customer->setAttributes([
            'customer_att1' => 'value1',
            'customer_att2' => 'value2',
            'customer_att3' => '',
            'customer_att4' => ['list', 'of', 'values'],
        ]);

        $this->assertEquals([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'vendor_customer_id' => '123',
            'language' => 'en',
            'marketing_consent' => true,
            'total_orders' => 5,
            'tags' => ['VIP', 'Loyal Customer'],
            'total_spent' => 1234.56,
            'attributes' => [
                'customer_att1' => 'value1',
                'customer_att2' => 'value2',
                'customer_att3' => '',
                'customer_att4' => ['list', 'of', 'values'],
            ],
        ], $customer->toArray());
    }

    public function testItReturnsAnArrayIgnoringEmptyFields()
    {
        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail('john@example.com');
        $customer->setVendorCustomerId('123');
        $customer->setLanguage('');
        $customer->setMarketingConsent(true);
        $customer->setTotalOrders(0);
        $customer->setTags([]);
        $customer->setTotalSpent(1234.56);

        $this->assertEquals([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'vendor_customer_id' => '123',
            'marketing_consent' => true,
            'total_orders' => 0,
            'total_spent' => 1234.56,
        ], $customer->toArray());
    }

    public function testItThrowsAnExceptionIfRequiredFieldsAreNotSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Required field "lastName" must be set');

        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setEmail('jon@example.com');

        $customer->toArray();
    }

    public function testItThrowsAnExceptionIfNonStringAttributeKeysAreSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Attribute keys must be strings, received: 1');

        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setEmail('jon@example.com');
        $customer->setAttributes([1 => 'value1']);

        $customer->toArray();
    }

    public function testItThrowsAnExceptionIfANullAttributeValueIsSet()
    {
        $this->expectException(UnboxException::class);
        $this->expectExceptionMessage('Received a null value for attribute "my_attribute"');

        $customer = new Customer();
        $customer->setFirstName('John');
        $customer->setEmail('jon@example.com');
        $customer->setAttributes(['my_attribute' => null]);

        $customer->toArray();
    }
}
