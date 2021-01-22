<?php

namespace MageSuite\Shipcloud\Test\Integration\Service;

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
     * @var \MageSuite\Shipcloud\Service\OrderShipmentGenerator
     */
    protected $orderShipmentGenerator;

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

        $this->shippingLabelGenerator = $this->objectManager->create(
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
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/shipment.php
     * @magentoConfigFixture default_store shipcloud/general/export_path var/out/print
     */
    public function testShipmentTrackingNumberAndPdfLabel()
    {
        $this->markTestSkipped('Needs to refactor test');

        $response = $this->shipmentResponse->addData([
            'id' => '3a186c51d4281acbecf5ed38805b1db92a9d668b',
            'carrier_tracking_no' => '84168117830018',
            'tracking_url' => 'https://track.shipcloud.io/3a186c51d4',
            'label_url' => 'http://localhost',
            'price' => 3.4
        ]);

        $this->getShipmentStub->expects($this->any())
            ->method('execute')
            ->willReturn($response);

        $order = $this->order->loadByIncrementId('100000001');
        $order->setData('number_of_packages', 1);
        $order->setData('shipping_method', 'dogma_dhl_classic_dogma_dhl_classic');

        $this->orderShipmentGenerator->processOrder($order);
        $shipcloudShipment = $this->shipcloudShipmentCollection
            ->addOrderFilter($order->getId())
            ->getFirstItem();
        $filePath = $shipcloudShipment->getLabelFilePath();
        $track = $order->getTracksCollection()
            ->getFirstItem();

        $this->assertEquals($response->getCarrierTrackingNo(), $shipcloudShipment->getCarrierTrackingNo());
        $this->assertEquals($response->getCarrierTrackingNo(), $track->getTrackNumber());
        $this->assertEquals(1, $order->getData('shipcloud_status'));
        $this->assertTrue(file_exists($filePath));
    }
}
