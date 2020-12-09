<?php

namespace MageSuite\Shipcloud\Api;

interface GetUserInterface
{
    /**
     * @return \MageSuite\Shipcloud\Api\Data\UserResponseInterface
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     */
    public function execute();
}
