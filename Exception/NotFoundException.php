<?php

namespace MageSuite\Shipcloud\Exception;

class NotFoundException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * The api endpoint you were trying to reach can't be found.
     * HTTP_CODE = 404
     */
}
