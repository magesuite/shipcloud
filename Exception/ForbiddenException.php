<?php

namespace MageSuite\Shipcloud\Exception;

class ForbiddenException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * You are not allowed to talk to this endpoint.
     * This can either be due to a wrong authentication or when you're trying to reach an endpoint that your account isn't allowed to access.
     * HTTP_CODE = 403
     */
}
