<?php

namespace StordUnbox\Model;

use StordUnbox\Exception\UnboxException;

class Order
{
    private const REQUIRED_FIELDS = ['id', 'number', 'createdAt', 'totalAmount', 'totalItems', 'currency'];

    /** @var string */
    private $id;

    /** @var string */
    private $number;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var float */
    private $totalAmount;

    /** @var int */
    private $totalItems;

    /** @var string */
    private $currency;

    /** @var string|null */
    private $billingCountry;

    /** @var string|null */
    private $billingPostcode;

    /** @var string|null */
    private $billingCity;

    /** @var string|null */
    private $shippingCountry;

    /** @var string|null */
    private $shippingPostcode;

    /** @var string|null */
    private $shippingCity;

    /** @var string|null */
    private $giftMessage;

    /** @var string|null */
    private $giftMessageRecipient;

    /** @var array|null */
    private $skus;

    /** @var array|null */
    private $productTitles;

    /** @var array|null */
    private $promoCodes;

    /** @var bool|null */
    private $subscriptionReorder;

    /** @var array|null */
    private $tags;

    /** @var array|null */
    private $attributes;

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function setTotalItems(int $totalItems): self
    {
        $this->totalItems = $totalItems;

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function setBillingCountry(string $billingCountry): self
    {
        $this->billingCountry = $billingCountry;

        return $this;
    }

    public function setBillingPostcode(string $billingPostcode): self
    {
        $this->billingPostcode = $billingPostcode;

        return $this;
    }

    public function setBillingCity(string $billingCity): self
    {
        $this->billingCity = $billingCity;

        return $this;
    }

    public function setShippingCountry(string $shippingCountry): self
    {
        $this->shippingCountry = $shippingCountry;

        return $this;
    }

    public function setShippingPostcode(string $shippingPostcode): self
    {
        $this->shippingPostcode = $shippingPostcode;

        return $this;
    }

    public function setShippingCity(string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    public function setGiftMessage(string $giftMessage): self
    {
        $this->giftMessage = $giftMessage;

        return $this;
    }

    public function setGiftMessageRecipient(string $giftMessageRecipient): self
    {
        $this->giftMessageRecipient = $giftMessageRecipient;

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function setSkus(array $skus): self
    {
        $this->skus = $this->itemsMustBeStrings($skus, 'skus');

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function setProductTitles(array $productTitles): self
    {
        $this->productTitles = $this->itemsMustBeStrings($productTitles, 'product titles');

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function setPromoCodes(array $promoCodes): self
    {
        $this->promoCodes = $this->itemsMustBeStrings($promoCodes, 'promo codes');

        return $this;
    }

    public function setSubscriptionReorder(bool $subscriptionReorder): self
    {
        $this->subscriptionReorder = $subscriptionReorder;

        return $this;
    }

    /**
     * @throws UnboxException
     */
    public function setTags(array $tags): self
    {
        $this->tags = $this->itemsMustBeStrings($tags, 'tags');

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
            'id' => $this->id,
            'number' => $this->number,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'total_amount' => $this->totalAmount,
            'total_items' => $this->totalItems,
            'currency' => $this->currency,
        ];

        $optionalFieldsWhenEmpty = [
            'billing_country' => 'billingCountry',
            'billing_postcode' => 'billingPostcode',
            'billing_city' => 'billingCity',
            'shipping_country' => 'shippingCountry',
            'shipping_postcode' => 'shippingPostcode',
            'shipping_city' => 'shippingCity',
            'gift_message' => 'giftMessage',
            'gift_message_recipient' => 'giftMessageRecipient',
            'skus' => 'skus',
            'product_titles' => 'productTitles',
            'promo_codes' => 'promoCodes',
            'is_subscription_reorder' => 'subscriptionReorder',
            'tags' => 'tags',
            'attributes' => 'attributes',
        ];

        foreach ($optionalFieldsWhenEmpty as $outputKey => $thisProp) {
            if (!empty($this->{$thisProp})) {
                $output[$outputKey] = $this->{$thisProp};
            }
        }

        if (isset($this->subscriptionReorder)) {
            $output['is_subscription_reorder'] = $this->subscriptionReorder;
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
    private function itemsMustBeStrings(array $items, string $fieldName): array
    {
        array_walk($items, function ($item, $key) use ($fieldName) {
            if (!is_string($item)) {
                throw new UnboxException('All ' . $fieldName . ' array items must be strings');
            }
        });

        return $items;
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
