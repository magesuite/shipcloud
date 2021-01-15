<?php

namespace MageSuite\Shipcloud\Exception;

class UnauthorizedException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * You didn't authorize with our api. Probably because you forgot to send your api key for authorizing at our api.
     * HTTP_CODE = 401
     */
}
