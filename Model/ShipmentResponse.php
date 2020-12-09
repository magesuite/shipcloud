<?php

namespace MageSuite\Shipcloud\Model;

class ShipmentResponse extends \Magento\Framework\DataObject implements \MageSuite\Shipcloud\Api\Data\ShipmentResponseInterface
{
    public function getId(): string
    {
        return (string)$this->getData(self::ID);
    }

    public function getCarrierTrackingNo(): string
    {
        return (string)$this->getData(self::CARRIER_TRACKING_NO);
    }

    public function getTrackingUrl(): string
    {
        return (string)$this->getData(self::TRACKING_URL);
    }

    public function getLabelUrl(): string
    {
        return (string)$this->getData(self::LABEL_URL);
    }

    public function getPrice(): float
    {
        return (float)$this->getData(self::PRICE);
    }
}
