<?php

namespace MageSuite\Shipcloud\Test\Integration\Controller\Adminhtml\Order;

class OrderShipmentGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\Shipcloud\Model\ShipmentResponse
     */
    protected $shipmentResponse;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment\Collection
     */
    protected $shipcloudShipmentCollection;

    /**
     * @var \MageSuite\Shipcloud\Model\GetShipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $getShipmentStub;

    /**
     * @var \MageSuite\Shipcloud\Model\GetShipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $getShipmentSuccessStub;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $curlStub;

    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $curlFactoryStub;

    /**
     * @var \MageSuite\Shipcloud\Service\ShippingLabelGenerator
     */
    protected $shippingLabelGenerator;

    /**
     * @var \MageSuite\Shipcloud\Service\ShippingLabelGenerator
     */
    protected $shippingLabelSuccessGenerator;

    /**
     * @var \MageSuite\Shipcloud\Service\OrderShipmentGenerator
     */
    protected $orderShipmentGenerator;

    /**
     * @var \MageSuite\Shipcloud\Service\OrderShipmentGenerator
     */
    protected $orderShipmentSuccessGenerator;

    /**
     * @var \MageSuite\Shipcloud\Controller\Adminhtml\Order\MassRetryExport
     */
    protected $controller;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Order
     */
    protected $shipcloudOrderResource;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $this->configuration = $this->objectManager->create(\MageSuite\Shipcloud\Helper\Configuration::class);
        $this->shipmentResponse = $this->objectManager->create(\MageSuite\Shipcloud\Model\ShipmentResponse::class);
        $this->shipcloudShipmentCollection = $this->objectManager->create(\MageSuite\Shipcloud\Model\ResourceModel\Shipment\Collection::class);

        $this->getShipmentStub = $this->getMockBuilder(\MageSuite\Shipcloud\Model\GetShipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->getShipmentSuccessStub = $this->getMockBuilder(\MageSuite\Shipcloud\Model\GetShipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->curlStub = $this->getMockBuilder(\Magento\Framework\HTTP\Client\Curl::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getStatus', 'getBody'])
            ->getMock();

        $this->curlFactoryStub = $this->getMockBuilder(\Magento\Framework\HTTP\Client\CurlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->curlStub->expects($this->any())
            ->method('getStatus')
            ->willReturn(200);

        $this->curlStub->expects($this->any())
            ->method('getBody')
            ->willReturn('dummy content');

        $this->curlFactoryStub->expects($this->any())
            ->method('create')
            ->willReturn($this->curlStub);

        $this->shippingLabelGenerator = $this->objectManager->create(\MageSuite\Shipcloud\Service\ShippingLabelGenerator::class);

        $this->shippingLabelSuccessGenerator = $this->objectManager->create(
            \MageSuite\Shipcloud\Service\ShippingLabelGenerator::class,
            [
                'curlFactory' => $this->curlFactoryStub
            ]
        );

        $this->orderShipmentGenerator = $this->objectManager->create(
            \MageSuite\Shipcloud\Service\OrderShipmentGenerator::class,
            [
                'getShipment' => $this->getShipmentStub,
                'shippingLabelGenerator' => $this->shippingLabelGenerator
            ]
        );

        $this->orderShipmentSuccessGenerator = $this->objectManager->create(
            \MageSuite\Shipcloud\Service\OrderShipmentGenerator::class,
            [
                'getShipment' => $this->getShipmentSuccessStub,
                'shippingLabelGenerator' => $this->shippingLabelSuccessGenerator
            ]
        );

        $this->controller = $this->objectManager->get(\MageSuite\Shipcloud\Controller\Adminhtml\Order\MassRetryExport::class);
        $this->shipcloudOrderResource = $this->objectManager->get(\MageSuite\Shipcloud\Model\ResourceModel\Order::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default_store shipcloud/general/export_path var/out/print
     * @magentoConfigFixture default/shipcloud/general/retry_limit 1
     */
    public function testMassActionWithOrder()
    {
        $this->markTestSkipped('Needs to refactor test');

        $this->getShipmentStub->expects($this->any())
            ->method('execute')
            ->willThrowException(new \MageSuite\Shipcloud\Exception\ShipcloudException(__('Error')));

        $order = $this->order->loadByIncrementId('100000001');
        $order->setData('number_of_packages', 1);
        $order->setData('is_exported_to_erp', 1);
        $order->setData('shipping_method', 'dogma_dhl_classic_dogma_dhl_classic');
        $order->save();
        $this->orderShipmentGenerator->execute();

        $order = $this->order->loadByIncrementId('100000001');
        $this->assertEquals(
            \MageSuite\Shipcloud\Service\OrderShipmentGenerator::STATUS_PENDING,
            $order->getData('shipcloud_status')
        );
        $this->assertTrue($this->shipcloudOrderResource->isRetryLimitReached($order));
        $this->controller->retrySendToShipcloud($order);
        $this->assertFalse($this->shipcloudOrderResource->isRetryLimitReached($order));

        $response = $this->shipmentResponse->addData([
            'id' => '3a186c51d4281acbecf5ed38805b1db92a9d668b',
            'carrier_tracking_no' => '84168117830018',
            'tracking_url' => 'https://track.shipcloud.io/3a186c51d4',
            'label_url' => 'http://localhost',
            'price' => 3.4
        ]);

        $this->getShipmentSuccessStub->expects($this->any())
            ->method('execute')
            ->willReturn($response);
        $this->orderShipmentSuccessGenerator->processOrder($order);
        $shipcloudShipment = $this->shipcloudShipmentCollection
            ->addOrderFilter($order->getId())
            ->getFirstItem();
        $filePath = $shipcloudShipment->getLabelFilePath();
        $track = $order->getTracksCollection()
            ->getFirstItem();

        $this->assertEquals($response->getCarrierTrackingNo(), $shipcloudShipment->getCarrierTrackingNo());
        $this->assertEquals($response->getCarrierTrackingNo(), $track->getTrackNumber());
        $this->assertEquals(
            \MageSuite\Shipcloud\Service\OrderShipmentGenerator::STATUS_GENERATED,
            $order->getData('shipcloud_status')
        );
    }
}
