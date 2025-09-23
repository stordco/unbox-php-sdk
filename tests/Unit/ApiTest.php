<?php

namespace Unit;

use StordUnbox\Api;
use StordUnbox\Exception\ApiException;
use StordUnbox\Exception\AuthenticationException;
use StordUnbox\Exception\ServerErrorException;
use StordUnbox\Exception\ServiceUnavailableException;
use StordUnbox\Model\Customer;
use StordUnbox\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class ApiTest extends TestCase
{
    /** @var MockObject|ClientInterface */
    private $mockClient;

    /** @var MockObject|RequestFactoryInterface */
    private $mockRequestFactory;

    /** @var MockObject|StreamFactoryInterface */
    private $mockStreamFactory;

    public function setUp(): void
    {
        $this->mockClient = $this->createMock(ClientInterface::class);
        $this->mockRequestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->mockStreamFactory = $this->createMock(StreamFactoryInterface::class);
    }

    public function testItSendsAStoreInstallRequest()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com'],
            200,
            ['success' => true]
        );

        $api->installStore('test-domain.com');
    }

    public function testItSendsAStoreInstallRequestToTestEndpoint()
    {
        $api = new Api(
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory,
            'pk-secret',
            true
        );

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.test.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com', 'origin_app_version' => '1.0.1'],
            200,
            ['success' => true]
        );

        $api->installStore('test-domain.com', '1.0.1');
    }

    public function testItSendsAStoreInstallRequestUsingAppVersionOnApi()
    {
        $api = new Api(
            $this->mockClient,
            $this->mockRequestFactory,
            $this->mockStreamFactory,
            'pk-secret',
            true,
            '1.5.4'
        );

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.test.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com', 'origin_app_version' => '1.5.4'],
            200,
            ['success' => true]
        );

        $api->installStore('test-domain.com');
    }

    public function testItThrowsAnExceptionIfServiceUnavailable()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com'],
            502,
            ['error' => 'we are not around right now']
        );

        $this->expectException(ServiceUnavailableException::class);

        $api->installStore('test-domain.com');
    }

    public function testItThrowsAnExceptionIfServerError()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com'],
            500,
            ['error' => 'oops']
        );

        $this->expectException(ServerErrorException::class);

        $api->installStore('test-domain.com');
    }

    public function testItThrowsAnExceptionIfAuthenticationError()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/install',
            ['shop_url' => 'test-domain.com'],
            401,
            ['error' => 'oops']
        );

        $this->expectException(AuthenticationException::class);

        $api->installStore('test-domain.com');
    }

    public function testItSendsAnOrder()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())
            ->method('toArray')
            ->willReturn([
                'id' => 123,
                'attributes' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ]
            ]);
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->expects($this->once())
            ->method('toArray')
            ->willReturn(['email' => 'john@example.com']);

        $content = [
            'order' => [
                'id' => 123,
                'attributes' => [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
            ],
            'customer' => ['email' => 'john@example.com'],
            'origin' => 'magento',
        ];

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/order',
            $content,
            202,
            ['success' => true]
        );

        $api->sendOrder($mockOrder, $mockCustomer, 'magento');
    }

    public function testItSendsAnOrderWithAnAppVersion()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())
            ->method('toArray')
            ->willReturn(['id' => 123]);
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->expects($this->once())
            ->method('toArray')
            ->willReturn(['email' => 'john@example.com']);

        $content = [
            'order' => ['id' => 123],
            'customer' => ['email' => 'john@example.com'],
            'origin' => 'magento',
            'origin_app_version' => '1.0.0',
        ];

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/order',
            $content,
            202,
            ['success' => true]
        );

        $api->sendOrder($mockOrder, $mockCustomer, 'magento', '1.0.0');
    }

    public function testItThrowsAnApiExceptionIfServerSideOrderValidationFails()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())
            ->method('toArray')
            ->willReturn(['id' => 123]);
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->expects($this->once())
            ->method('toArray')
            ->willReturn(['email' => 'john@example.com']);

        $content = [
            'order' => ['id' => 123],
            'customer' => ['email' => 'john@example.com'],
            'origin' => 'magento',
        ];

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/ingest/order',
            $content,
            422,
            ['errors' => ['order.number' => 'required field is missing']]
        );

        $this->expectException(ApiException::class);

        $api->sendOrder($mockOrder, $mockCustomer, 'magento');
    }

    public function testItRequestsAPrint()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $merchantId = 'MERCHANT_X';
        $locationId = 'LOCATION_Y';
        $orderId = '#42';

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/fulfilment/orders/print',
            [
                'order_id' => $orderId,
                'location_id' => $locationId,
                'merchant_id' => $merchantId,
                'retry' => false,
            ],
            200,
            []
        );

        $api->requestPrint($orderId, $locationId, $merchantId, false);
    }

    public function testItRequestsAPrintWithOnlyAnOrderId()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $orderId = '#42';

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/fulfilment/orders/print',
            [
                'order_id' => $orderId,
                'location_id' => null,
                'merchant_id' => null,
                'retry' => true,
            ],
            200,
            []
        );

        $api->requestPrint($orderId);
    }

    public function testItRequestsABatchPrint()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $merchantId = 'MERCHANT_X';
        $locationId = 'LOCATION_Y';
        $orderIds = ['#42', '#24', '#84'];

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/fulfilment/orders/batch-print',
            [
                'order_ids' => $orderIds,
                'location_id' => $locationId,
                'merchant_id' => $merchantId,
                'retry' => false,
            ],
            200,
            []
        );

        $api->requestBatchPrint($orderIds, $locationId, $merchantId, false);
    }

    public function testItRequestsABatchPrintWithOnlyOrderIds()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $orderIds = ['#42', '#24', '#84'];

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'POST',
            'https://api.pennyblack.io/fulfilment/orders/batch-print',
            [
                'order_ids' => $orderIds,
                'location_id' => null,
                'merchant_id' => null,
                'retry' => true,
            ],
            200,
            []
        );

        $api->requestBatchPrint($orderIds);
    }

    public function testItGetsOrderPrintStatus()
    {
        $api = new Api($this->mockClient, $this->mockRequestFactory, $this->mockStreamFactory, 'pk-secret');

        $this->mockClientRequestAndResponse(
            'pk-secret',
            'GET',
            'https://api.pennyblack.io/fulfilment/orders/MERCHANT_ID/42',
            [],
            200,
            ['status' => 'no print']
        );

        $output = $api->getOrderPrintStatus('MERCHANT_ID', '42');
        $this->assertEquals($output, ['status' => 'no print']);
    }

    private function mockClientRequestAndResponse($apiKey, $method, $url, $content, $statusCode, $responseContent)
    {
        if ('GET' !== $method) {
            $mockStream = $this->createMock(StreamInterface::class);
            $this->mockStreamFactory
                ->expects($this->once())
                ->method('createStream')
                ->with(json_encode($content))
                ->willReturn($mockStream);
        }

        $mockRequest = $this->createMock(RequestInterface::class);
        $this->mockRequestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with($method, $url)
            ->willReturn($mockRequest);

        if ('GET' === $method) {
            $mockRequest->expects($this->once())
                ->method('withHeader')
                ->with('X-Api-Key', $apiKey)
                ->willReturn($mockRequest);
        } else {
            $mockRequest->expects($this->exactly(2))
                ->method('withHeader')
                ->withConsecutive(
                    ['X-Api-Key', $apiKey],
                    ['Content-Type', 'application/json']
                )
                ->willReturnOnConsecutiveCalls($mockRequest, $mockRequest);

            $mockRequest->expects($this->once())
                ->method('withBody')
                ->with($mockStream)
                ->willReturn($mockRequest);
        }

        $mockResponse = $this->createMock(ResponseInterface::class);
        $this->mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($mockRequest)
            ->willReturn($mockResponse);

        $mockResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn($statusCode);

        if ($statusCode !== 401 && $statusCode !== 403) {
            $mockBody = $this->createMock(StreamInterface::class);
            $mockResponse->expects($this->once())
                ->method('getBody')
                ->willReturn($mockBody);

            $mockBody->expects($this->once())
                ->method('getContents')
                ->willReturn(json_encode($responseContent));
        }
    }
}
