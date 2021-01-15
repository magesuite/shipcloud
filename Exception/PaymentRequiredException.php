<?php

namespace MageSuite\Shipcloud\Exception;

class PaymentRequiredException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * You've reached the maximum of your current plan. Please upgrade to a higher plan.
     * HTTP_CODE = 402
     */
}
