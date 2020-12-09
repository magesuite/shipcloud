<?php

namespace MageSuite\Shipcloud\Exception;

class UnprocessableEntityException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Your request was well-formed but couldn't be followed due to semantic errors. Please see the response body for more detailed information.
     * HTTP_CODE = 422
     */
}
