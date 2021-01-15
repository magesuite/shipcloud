<?php

namespace MageSuite\Shipcloud\Api;

interface GetShipmentInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \MageSuite\Shipcloud\Api\Data\ShipmentResponseInterface
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     */
    public function execute(\Magento\Sales\Model\Order $order);
}
