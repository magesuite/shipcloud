<?php

namespace MageSuite\Shipcloud\Service;

class TrackingNumberProcessor
{
    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackingFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $notifier;

    /**
     * @var \MageSuite\Shipcloud\Model\CarrierList
     */
    protected $carrierList;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackingFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Shipping\Model\ShipmentNotifier $notifier,
        \MageSuite\Shipcloud\Model\CarrierList $carrierList,
        \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory,
        \MageSuite\Shipcloud\Helper\Configuration $configuration
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->trackingFactory = $trackingFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->notifier = $notifier;
        $this->carrierList = $carrierList;
        $this->collectionFactory = $collectionFactory;
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Sales\Model\Order $order)
    {
        $orderId = $order->getId();

        $shipcloudShipmentCollection = $this->collectionFactory->create();
        $shipcloudShipmentCollection->addOrderFilter($orderId);

        foreach ($shipcloudShipmentCollection as $shipcloudShipment) {
            if (!$shipcloudShipment->getLabelFilename()) {
                throw new \Exception(__('Print label is not downloaded yet!'));
            }

            $this->processShipment($shipcloudShipment, $order);
        }
    }

    /**
     * @param \MageSuite\Shipcloud\Model\Shipment $shipcloudShipment
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function processShipment(
        \MageSuite\Shipcloud\Model\Shipment $shipcloudShipment,
        \Magento\Sales\Model\Order $order
    ) {
        if ($order->hasShipments()) {
            $shipment = $order->getShipmentsCollection()->getFirstItem();

            // reload from repository to fetch extension attributes that are not present in a collection
            $shipmentId = $shipment->getData('entity_id');
            $shipment = $this->shipmentRepository->get($shipmentId);
        } else {
            $shipment = $this->createShipment($order);
        }

        $shipmentTracks = $shipment->getAllTracks();

        foreach ($shipmentTracks as $track) {
            if ($track->getTrackNumber() == $shipcloudShipment->getCarrierTrackingNo()) {
                return $shipment;
            }
        }

        $carrier = $this->getCarrier($order);
        $tracking = $this->trackingFactory->create();
        $tracking->setDescription($shipcloudShipment->getTrackingUrl());
        $tracking->setTrackNumber($shipcloudShipment->getCarrierTrackingNo());
        $tracking->setCarrierCode('custom');

        if ($carrier) {
            $tracking->setTitle($carrier->getName());
        }

        $shipment->addTrack($tracking);
        $this->shipmentRepository->save($shipment);

        if ($this->configuration->getSendShipmentEmailFlag()) {
            $this->notifier->notify($shipment);
        }

        return $shipment;
    }

    protected function createShipment(\Magento\Sales\Model\Order $order)
    {
        $items = [];

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $items[$orderItem->getId()] = $orderItem->getQtyToShip();
        }

        $shipment = $this->shipmentFactory->create($order, $items);
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $shipment->save();
        $shipment->getOrder()->save();

        return $shipment;
    }

    protected function getCarrier(\Magento\Sales\Model\Order $order)
    {
        return $this->carrierList->getOrderCarrier($order);
    }
}
