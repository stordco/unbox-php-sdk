<?php

namespace StordUnbox;

use StordUnbox\Exception\ApiException;
use StordUnbox\Exception\AuthenticationException;
use StordUnbox\Exception\UnboxException;
use StordUnbox\Exception\ServerErrorException;
use StordUnbox\Exception\ServiceUnavailableException;
use StordUnbox\Model\Customer;
use StordUnbox\Model\Order;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Api
{
    private const PROD_URL = 'https://api.pennyblack.io/';
    private const TEST_URL = 'https://api.test.pennyblack.io/';

    private const MAX_RETRIES = 3;
    private const SUCCESS_RESPONSE_CODES = [200, 201, 202, 204];

    /** @var ClientInterface */
    private $httpClient;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /**
     * A version number of your client plugin (not the platform, but your integration)
     * that can help Penny Black support with debugging.
     * To offer flexibility in implementation, this can be passed either when instantiating the API client,
     * or on the endpoints that use them directly.
     * @var string
     */
    private $originAppVersion;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $apiKey,
        bool $isTest = false,
        string $originAppVersion = ''
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->apiKey = $apiKey;
        $this->baseUrl = $isTest ? self::TEST_URL : self::PROD_URL;
        $this->originAppVersion = $originAppVersion;
    }

    /**
     * @see https://pennyblack.stoplight.io/docs/pennyblack/ingest/operations/create-a-install
     *
     * @throws UnboxException
     */
    public function installStore(string $shopUrl, string $originAppVersion = ''): void
    {
        $params = ['shop_url' => $shopUrl];
        if ($originAppVersion) {
            $params['origin_app_version'] = $originAppVersion;
        } elseif ($this->originAppVersion) {
            $params['origin_app_version'] = $this->originAppVersion;
        }

        $this->sendPostRequest('ingest/install', $params);
    }

    /**
     * Send an order with a retry mechanism for errors that are possibly down to network transmission.
     *
     * @see https://pennyblack.stoplight.io/docs/pennyblack/ingest/operations/create-a-order
     *
     * @throws UnboxException
     */
    public function sendOrder(Order $order, Customer $customer, string $origin, string $originAppVersion = ''): void
    {
        $params = [
            'order' => $order->toArray(),
            'customer' => $customer->toArray(),
            'origin' => $origin
        ];
        if ($originAppVersion) {
            $params['origin_app_version'] = $originAppVersion;
        } elseif ($this->originAppVersion) {
            $params['origin_app_version'] = $this->originAppVersion;
        }

        $this->sendPostRequestWithRetries('ingest/order', $params);
    }

    /**
     * @see https://pennyblack.stoplight.io/docs/pennyblack/fulfilment/operations/create-a-fulfilment-order-print
     *
     * @return string The message to indicate the action taken
     *
     * @throws UnboxException
     */
    public function requestPrint(
        string $orderId,
        ?string $locationId = null,
        ?string $merchantId = null,
        bool $retry = true
    ): string {
        $content = [
            'order_id' => $orderId,
            'location_id' => $locationId,
            'merchant_id' => $merchantId,
            'retry' => $retry,
        ];

        $output = $this->sendPostRequest('fulfilment/orders/print', $content);

        if (isset($output['message'])) {
            return (string) $output['message'];
        }
        return print_r($output, true);
    }

    /**
     * @see https://pennyblack.stoplight.io/docs/pennyblack/fulfilment/operations/create-a-fulfilment-order-batch-print
     *
     * @return array "batchId" and "message" keys
     *
     * @throws UnboxException
     */
    public function requestBatchPrint(
        array $orderIds,
        ?string $locationId = null,
        ?string $merchantId = null,
        bool $retry = true
    ): array {
        $content = [
            'order_ids' => $orderIds,
            'location_id' => $locationId,
            'merchant_id' => $merchantId,
            'retry' => $retry,
        ];

        return $this->sendPostRequest('fulfilment/orders/batch-print', $content);
    }

    /**
     * @see https://pennyblack.stoplight.io/docs/pennyblack/fulfilment/operations/get-a-fulfilment-order
     *
     * @throws UnboxException
     */
    public function getOrderPrintStatus(string $merchantId, string $orderId): array
    {
        return $this->sendGetRequest('fulfilment/orders/' . $merchantId . '/' . $orderId);
    }

    /**
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ServerErrorException
     * @throws ServiceUnavailableException
     */
    private function sendPostRequestWithRetries(string $path, $content): array
    {
        $numAttempts = 0;

        while (1) {
            try {
                return $this->sendPostRequest($path, $content);
            } catch (ServerErrorException | ServiceUnavailableException $e) {
                ++$numAttempts;

                if ($numAttempts >= self::MAX_RETRIES) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ServerErrorException
     * @throws ServiceUnavailableException
     */
    private function sendPostRequest(string $path, $content): array
    {
        $body = $this->streamFactory->createStream(json_encode($content));
        $request = $this->requestFactory->createRequest('POST', $this->baseUrl . ltrim($path, '/'))
            ->withHeader('X-Api-Key', $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        /** @var RequestInterface $request */
        return $this->sendRequest($request);
    }

    /**
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ServerErrorException
     * @throws ServiceUnavailableException
     */
    private function sendGetRequest($path): array
    {
        $request = $this->requestFactory->createRequest('GET', $this->baseUrl . ltrim($path, '/'))
            ->withHeader('X-Api-Key', $this->apiKey);

        /** @var RequestInterface $request */
        return $this->sendRequest($request);
    }

    /**
     * @throws ServiceUnavailableException
     * @throws ServerErrorException
     * @throws AuthenticationException
     * @throws ApiException
     */
    private function sendRequest(RequestInterface $request): array
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode > 500) {
            throw new ServiceUnavailableException($response->getBody()->getContents(), $statusCode);
        }

        if ($statusCode === 500) {
            throw new ServerErrorException($response->getBody()->getContents());
        }

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthenticationException($statusCode);
        }

        if (in_array($statusCode, self::SUCCESS_RESPONSE_CODES)) {
            $output = json_decode($response->getBody()->getContents(), true);
            if (is_array($output)) {
                return $output;
            }
            if (!$output) {
                return [];
            }
            return [$output];
        }

        throw new ApiException($response->getBody()->getContents(), $statusCode);
    }
}
