<?php

namespace MageSuite\Shipcloud\Api\Data;

interface OrderInterface
{
    const ORDER_ID = 'order_id';
    const RETRY_COUNT = 'retry_count';
    const UPDATED_AT = 'updated_at';

    public function getRetryCount();

    public function setRetryCount($retryCount);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
