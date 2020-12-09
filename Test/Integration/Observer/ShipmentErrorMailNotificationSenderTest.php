<?php

namespace MageSuite\Shipcloud\Test\Integration\Observer;

class ShipmentErrorMailNotificationSenderTest extends \PHPUnit\Framework\TestCase
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
     * @var \MageSuite\Shipcloud\Observer\ShipmentErrorMailNotificationSender
     */
    protected $shipcloudErrorNotificationsObserver;

    /**
     * @var \MageSuite\Shipcloud\Service\ErrorNotificationSender
     */
    protected $errorNotificationSenderStub;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManagerStub;


    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->order = $this->objectManager->create(\Magento\Sales\Model\Order::class);

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

        $this->eventManagerStub = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $this->shippingLabelGenerator = $this->objectManager->create(\MageSuite\Shipcloud\Service\ShippingLabelGenerator::class);

        $this->orderShipmentGenerator = $this->objectManager->create(
            \MageSuite\Shipcloud\Service\OrderShipmentGenerator::class,
            [
                'getShipment' => $this->getShipmentStub,
                'shippingLabelGenerator' => $this->shippingLabelGenerator,
                'eventManager' => $this->eventManagerStub
            ]
        );

        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)->disableOriginalConstructor()->getMock();

        $transportBuilder = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)->disableOriginalConstructor()->getMock();
        $transportBuilder->method('setTemplateIdentifier')->willReturn($transportBuilder);
        $transportBuilder->method('setTemplateOptions')->willReturn($transportBuilder);
        $transportBuilder->method('setTemplateVars')->willReturn($transportBuilder);
        $transportBuilder->method('setFromByScope')->willReturn($transportBuilder);
        $transportBuilder->method('addTo')->willReturn($transportBuilder);
        $transportBuilder->method('setReplyTo')->willReturn($transportBuilder);
        $transportBuilder->method('getTransport')->willReturn($transport);

        $this->errorNotificationSenderStub = $this->objectManager->create(
            \MageSuite\Shipcloud\Service\ErrorNotificationSender::class,
            ['transportBuilder' => $transportBuilder]);

        $this->shipcloudErrorNotificationsObserver =
            $this->objectManager->create(
                \MageSuite\Shipcloud\Observer\ShipmentErrorMailNotificationSender::class,
                ['errorNotificationSender' => $this->errorNotificationSenderStub]
            );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default/trans_email/shipcloud_email/email test@test.com
     * @magentoConfigFixture default/trans_email/shipcloud_email/name test@test.com
     * @magentoConfigFixture default_store shipcloud/general/export_path var/out/print
     * @magentoConfigFixture default/shipcloud/general/retry_limit 1
     */
    public function testSendEmailNotificationAfterShipcloudErrorApiRequest()
    {
        $this->getShipmentStub->expects($this->any())
            ->method('execute')
            ->willThrowException(new \MageSuite\Shipcloud\Exception\ShipcloudException(__('Shipcloud Error')));

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->disableOriginalConstructor()->getMock();

        $eventObserver->expects($this->exactly(2))->method('getData')->withAnyParameters()->willReturn('test');

        $this->shipcloudErrorNotificationsObserver->execute($eventObserver);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default/trans_email/shipcloud_email/email test@test.com
     * @magentoConfigFixture default/trans_email/shipcloud_email/name test@test.com
     * @magentoConfigFixture default_store shipcloud/general/export_path var/out/print
     * @magentoConfigFixture default/shipcloud/general/retry_limit 1
     */
    public function testDispatchEventErrorNotificationAfterExceptionApiRequest()
    {
        $this->eventManagerStub->expects($this->any())->method('dispatch')->willReturn($this->orderShipmentGenerator);

        $this->getShipmentStub->expects($this->any())
            ->method('execute')
            ->willThrowException(new \Exception(__('General Exception')));

        $order = $this->order->loadByIncrementId('100000001');

        $this->orderShipmentGenerator->execute();
    }
}
