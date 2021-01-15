<?php

namespace MageSuite\Shipcloud\Exception;

class ShipcloudException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Something has seriously gone wrong. Don't worry, we'll have a look at it.
     * HTTP_CODE = 500
     *
     * Something has gone wrong while talking to the carrier backend. Please see the response body for more detailed information.
     * HTTP_CODE = 502
     *
     * Unfortunately we couldn't connect to the carrier backend. It is either very slow or not reachable at all.
     * If you want to stay informed about the carrier status, follow our developer twitter account at @shipcloud_devs
     * HTTP_COD = 504
     */
}
