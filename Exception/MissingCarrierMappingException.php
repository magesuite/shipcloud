<?php

namespace MageSuite\Shipcloud\Exception;

class MissingCarrierMappingException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * Carrier code is missing in carriers list. Processing order will be stopped.
     */
}
