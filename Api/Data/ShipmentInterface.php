<?php

namespace MageSuite\Shipcloud\Api\Data;

interface ShipmentInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const SHIPCLOUD_ID = 'shipcloud_id';
    const CARRIER_TRACKING_NO = 'carrier_tracking_no';
    const TRACKING_URL = 'tracking_url';
    const LABEL_URL = 'label_url';
    const PRICE = 'price';
    const LABEL_FILENAME = 'label_filename';
    const PACKAGE_NUMBER = 'package_number';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getOrderId();

    public function setOrderId($orderId);

    public function getShipcloudId();

    public function setShipcloudId($shipcloudId);

    public function getCarrierTrackingNo();

    public function setCarrierTrackingNo($carrierTrackingNo);

    public function getTrackingUrl();

    public function setTrackingUrl($trackingUrl);

    public function getLabelUrl();

    public function setLabelUrl($labelUrl);

    public function getPrice();

    public function setPrice($price);

    public function getLabelFilename();

    public function setLabelFilename($labelFilename);

    public function getPackageNumber();

    public function setPackageNumber($packageNumber);

    public function getCreatedAt();

    public function setCreatedAt($createdAt);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
