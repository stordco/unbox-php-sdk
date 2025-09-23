<?php

namespace StordUnbox\Model;

use StordUnbox\Exception\UnboxException;

class Customer
{
    private const REQUIRED_FIELDS = ['firstName', 'lastName', 'email'];

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email;

    /** @var string|null */
    private $vendorCustomerId;

    /** @var string|null */
    private $language;

    /** @var bool|null */
    private $marketingConsent;

    /** @var int|null */
    private $totalOrders;

    /** @var array|null */
    private $tags;

    /** @var float|null */
    private $totalSpent;

    /** @var array|null */
    private $attributes;

    public function setVendorCustomerId(string $vendorCustomerId): self
    {
        $this->vendorCustomerId = $vendorCustomerId;

        return $this;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setMarketingConsent(bool $marketingConsent): self
    {
        $this->marketingConsent = $marketingConsent;

        return $this;
    }

    public function setTotalOrders(int $totalOrders): self
    {
        $this->totalOrders = $totalOrders;

        return $this;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function setTotalSpent(float $totalSpent): self
    {
        $this->totalSpent = $totalSpent;

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $this->validateAttributes($attributes);

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function toArray(): array
    {
        $this->validateRequiredFields();

        $output = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
        ];

        $optionalFieldsWhenEmpty = [
            'vendor_customer_id' => 'vendorCustomerId',
            'language' => 'language',
            'tags' => 'tags',
            'attributes' => 'attributes',
        ];

        foreach ($optionalFieldsWhenEmpty as $outputKey => $thisProp) {
            if (!empty($this->{$thisProp})) {
                $output[$outputKey] = $this->{$thisProp};
            }
        }

        if (isset($this->marketingConsent)) {
            $output['marketing_consent'] = $this->marketingConsent;
        }
        if (isset($this->totalOrders)) {
            $output['total_orders'] = $this->totalOrders;
        }
        if (isset($this->totalSpent)) {
            $output['total_spent'] = $this->totalSpent;
        }

        return $output;
    }

    /**
     * @throws UnboxException
     */
    private function validateRequiredFields(): void
    {
        foreach (self::REQUIRED_FIELDS as $requiredField) {
            if (!isset($this->{$requiredField})) {
                throw new UnboxException('Required field "' . $requiredField . '" must be set');
            }
        }
    }

    /**
     * @throws UnboxException
     */
    private function validateAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (!is_string($key)) {
                throw new UnboxException('Attribute keys must be strings, received: ' . $key);
            }

            if (is_null($value)) {
                throw new UnboxException(sprintf('Received a null value for attribute "%s"', $key));
            }
        }

        return $attributes;
    }
}
