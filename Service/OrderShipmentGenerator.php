<?php

namespace MageSuite\Shipcloud\Service;

class OrderShipmentGenerator
{
    const STATUS_PENDING = 0;
    const STATUS_GENERATED = 1;
    const STATUS_ERROR = 2;
    const STATUS_IGNORED = 3;
    const ERROR_NOTIFICATION_EVENT_NAME = 'shipcloud_shipment_label_error_notification';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResourceModel;

    /**
     * @var \MageSuite\Shipcloud\Model\GetShipment
     */
    protected $getShipment;

    /**
     * @var \MageSuite\Shipcloud\Model\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment
     */
    protected $shipmentResourceModel;

    /**
     * @var \MageSuite\Shipcloud\Service\ShippingLabelGenerator
     */
    protected $shippingLabelGenerator;

    /**
     * @var \MageSuite\Shipcloud\Service\TrackingNumberProcessor
     */
    protected $trackingNumberProcessor;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Order
     */
    protected $shipcloudOrderResource;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
        \MageSuite\Shipcloud\Api\GetShipmentInterface $getShipment,
        \MageSuite\Shipcloud\Model\ShipmentFactory $shipmentFactory,
        \MageSuite\Shipcloud\Model\ResourceModel\Shipment $shipmentResourceModel,
        \MageSuite\Shipcloud\Service\ShippingLabelGenerator $shippingLabelGenerator,
        \MageSuite\Shipcloud\Service\TrackingNumberProcessor $trackingNumberProcessor,
        \MageSuite\Shipcloud\Model\ResourceModel\Order $shipcloudOrderResource,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->getShipment = $getShipment;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentResourceModel = $shipmentResourceModel;
        $this->shippingLabelGenerator = $shippingLabelGenerator;
        $this->trackingNumberProcessor = $trackingNumberProcessor;
        $this->shipcloudOrderResource = $shipcloudOrderResource;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    public function execute($orderId = null)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('number_of_packages', ['gt' => 0])
            //->addAttributeToFilter('shipcloud_status', self::STATUS_PENDING) //TODO: UNCOMMENT IT
            ->setPageSize(500);

        if ($orderId) {
            $collection->addAttributeToFilter('entity_id', $orderId);
        }

        $this->shipcloudOrderResource->addRetryCountLimit($collection);

        $lastPage = $collection->getSize();
        $page = 1;

        while ($page <= $lastPage) {
            $collection->setCurPage($page)->load();

            foreach ($collection as $order) {
                try {
                    $this->processOrder($order);
                } catch (
                    \MageSuite\Shipcloud\Exception\UnauthorizedException
                    | \MageSuite\Shipcloud\Exception\PaymentRequiredException
                    | \MageSuite\Shipcloud\Exception\ForbiddenException
                    | \MageSuite\Shipcloud\Exception\NotFoundException $e
                ) {
                    $this->logger->error($e->getMessage());
                    $this->dispatchEvent(self::ERROR_NOTIFICATION_EVENT_NAME, $order->getId(), $e->getMessage());
                    throw new $e;
                } catch (\MageSuite\Shipcloud\Exception\ShipcloudException $e) {
                    $this->shipcloudOrderResource->incrementRetryCount($order);
                    $this->logger->error($e->getMessage());
                    $this->dispatchEvent(self::ERROR_NOTIFICATION_EVENT_NAME, $order->getId(), $e->getMessage());
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->dispatchEvent(self::ERROR_NOTIFICATION_EVENT_NAME, $order->getId(), $e->getMessage());
                }
            }

            $collection->clear();
            $page++;
        }
    }

    public function processOrder(\Magento\Sales\Model\Order $order)
    {
        $numOfPackages = $this->getNumberOfPackages($order);

        try {
            for ($numStart = 0; $numStart < $numOfPackages; $numStart++) {
                /** @var \MageSuite\Shipcloud\Model\ShipmentResponse $response */
                $response = $this->getShipment->execute($order);
                $lastPackageNumber = $this->shipmentResourceModel->getLastPackageNumber($order->getId());
                /** @var \MageSuite\Shipcloud\Model\Shipment $shipment */
                $shipment = $this->shipmentFactory->create()
                    ->setOrderId($order->getId())
                    ->setShipcloudId($response->getId())
                    ->setCarrierTrackingNo($response->getCarrierTrackingNo())
                    ->setTrackingUrl($response->getTrackingUrl())
                    ->setLabelUrl($response->getLabelUrl())
                    ->setPrice($response->getPrice())
                    ->setPackageNumber($lastPackageNumber + 1);
                $this->shipmentResourceModel->save($shipment);
            }

            $this->shippingLabelGenerator->execute($order);
            $this->trackingNumberProcessor->execute($order);
            $order->setData('shipcloud_status', self::STATUS_GENERATED);
            $this->addOrderComment($order, __('Shipment has been created.'));
        } catch (\MageSuite\Shipcloud\Exception\MissingParameterException
            |\MageSuite\Shipcloud\Exception\MissingCarrierMappingException $e) {
            $order->setData('shipcloud_status', self::STATUS_IGNORED);
            $this->dispatchEvent(self::ERROR_NOTIFICATION_EVENT_NAME, $order->getId(), $e->getMessage());
            $this->addOrderComment($order, $e->getMessage());
            return $this;
        } catch (\MageSuite\Shipcloud\Exception\BadRequestException
            | \MageSuite\Shipcloud\Exception\UnprocessableEntityException $e
        ) {
            $order->setData('shipcloud_status', self::STATUS_ERROR);
            $this->dispatchEvent(self::ERROR_NOTIFICATION_EVENT_NAME, $order->getId(), $e->getMessage());
            $this->addOrderComment($order, $e->getMessage());
            return $this;
        }

        $this->eventManager->dispatch(
            'shipcloud_order_shipment_generate_after',
            ['order' => $order]
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return int
     */
    protected function getNumberOfPackages(\Magento\Sales\Model\Order $order)
    {
        $numberOfPackages = (int)$order->getData('number_of_packages');
        $existingShipments = $this->shipmentResourceModel->getNumberOfShipments($order->getId());

        return $numberOfPackages - $existingShipments;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $comment
     * @return $this
     * @throws \Exception
     */
    protected function addOrderComment(\Magento\Sales\Model\Order $order, $comment)
    {
        $historyComment[] = 'SHIPCLOUD API:';
        $historyComment[] = $comment;
        $order->addStatusHistoryComment(implode('<br>', $historyComment));
        $order->save();

        return $this;
    }


    /**
     * @param string $eventName
     * @param string $orderId
     * @param string $errorMessage
     * @return $this
     */
    protected function dispatchEvent($eventName, $orderId, $errorMessage)
    {
        $this->eventManager->dispatch(
            $eventName,
            ['orderId' => $orderId, 'error' => $errorMessage]
        );

        return $this;
    }
}
