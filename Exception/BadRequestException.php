<?php

namespace MageSuite\Shipcloud\Exception;

class BadRequestException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Your request was not correct. Please see the response body for more detailed information.
     * HTTP_CODE = 400
     */
}
