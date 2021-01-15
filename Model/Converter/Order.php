<?php

namespace MageSuite\Shipcloud\Model\Converter;

class Order
{
    const PACKAGE_TYPE = 'parcel';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \MageSuite\Shipcloud\Model\CarrierList
     */
    protected $carrierList;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MageSuite\Shipcloud\Model\CarrierList $carrierList,
        \MageSuite\Shipcloud\Helper\Configuration $configuration
    ) {
        $this->eventManager = $eventManager;
        $this->carrierList = $carrierList;
        $this->configuration = $configuration;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     * @throws \Exception
     */
    public function toShipment(\Magento\Sales\Model\Order $order)
    {
        $originAddress = $this->configuration->getOriginAddress();
        $shippingAddress = $order->getShippingAddress();
        $carrier = $this->getCarrier($order);
        $params = [
            'from' => [
                'company' => (string)$originAddress->getCompany(),
                'first_name' => (string)$originAddress->getFirstname(),
                'last_name' => (string)$originAddress->getLastname(),
                'care_of' => (string)$originAddress->getCareOf(),
                'street' => (string)$originAddress->getStreet(),
                'street_no' => (string)$originAddress->getStreetNumber(),
                'city' => (string)$originAddress->getCity(),
                'zip_code' => (string)$originAddress->getPostcode(),
                'state' => (string)$originAddress->getRegion(),
                'country' => (string)$originAddress->getCountryId(),
                'phone' => (string)$originAddress->getTelephone()
            ],
            'to' => [
                'company' => (string)$shippingAddress->getCompany(),
                'first_name' => (string)$shippingAddress->getFirstname(),
                'last_name' => (string)$shippingAddress->getLastname(),
                'street' => (string)$shippingAddress->getStreetLine(1),
                'street_no' => (string)$shippingAddress->getStreetLine(2),
                'city' => (string)$shippingAddress->getCity(),
                'zip_code' => (string)$shippingAddress->getPostcode(),
                'country' => (string)$shippingAddress->getCountryId(),
                'phone' => (string)$shippingAddress->getTelephone(),
                'email' => (string)$order->getCustomerEmail()
            ],
            'package' => [
                'weight' => ($this->configuration->getMaximumPackageWeight()/1000),
                'length' => $this->configuration->getPackageLength(),
                'width' => $this->configuration->getPackageWidth(),
                'height' => $this->configuration->getPackageHeight(),
                'type' => self::PACKAGE_TYPE
            ],
            'label' => [
                'format' => $this->configuration->getLabelFormat()
            ],
            'carrier' => $carrier->getName(),
            'service' => $carrier->getService(),
            'reference_number' => $order->getIncrementId(),
            'notification_email' => $order->getCustomerEmail(),
            'create_shipping_label' => true
        ];
        $paramsObj = new \Magento\Framework\DataObject($params);
        $this->eventManager->dispatch(
            'shipcloud_converter_order_to_shipment_after',
            ['params' => $paramsObj]
        );

        return $paramsObj->getData();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \MageSuite\Shipcloud\Model\Carrier
     * @throws \Exception
     */
    protected function getCarrier(\Magento\Sales\Model\Order $order)
    {
        $carrier = $this->carrierList->getOrderCarrier($order);

        if (!$carrier instanceof \MageSuite\Shipcloud\Model\Carrier) {
            throw new \MageSuite\Shipcloud\Exception\MissingCarrierMappingException(
                __('Carrier missing in carriers mapping. Processing order is stopped.')
            );
        }

        return $carrier;
    }
}
