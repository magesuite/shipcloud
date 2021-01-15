<?php

namespace MageSuite\Shipcloud\Service;

class TrackingNumberProcessor
{
    /**
     * @var \Magento\Sales\Model\Convert\OrderFactory
     */
    protected $convertOrderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackingFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var \MageSuite\Shipcloud\Model\CarrierList
     */
    protected $carrierList;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackingFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \MageSuite\Shipcloud\Model\CarrierList $carrierList,
        \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory
    ) {
        $this->convertOrderFactory = $convertOrderFactory;
        $this->trackingFactory = $trackingFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->carrierList = $carrierList;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(\Magento\Sales\Model\Order $order)
    {
        $shipcloudShipmentCollection = $this->collectionFactory->create()
            ->addOrderFilter($order->getId());

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
        } else {
            $shipment = $this->createShipment($order);
        }

        foreach ($shipment->getAllTracks() as $track) {
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

        return $shipment;
    }

    protected function createShipment(\Magento\Sales\Model\Order $order)
    {
        $convertOrder = $this->convertOrderFactory->create();
        $shipment = $convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }

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
