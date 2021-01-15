<?php

namespace MageSuite\Shipcloud\Api\Data;

interface ShipmentResponseInterface
{
    const ID = 'id';
    const CARRIER_TRACKING_NO = 'carrier_tracking_no';
    const TRACKING_URL = 'tracking_url';
    const LABEL_URL = 'label_url';
    const PRICE = 'price';

    public function getId(): string;

    public function getCarrierTrackingNo(): string;

    public function getTrackingUrl(): string;

    public function getLabelUrl(): string;

    public function getPrice(): float;
}
