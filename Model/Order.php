<?php

namespace MageSuite\Shipcloud\Model;

class Order extends \Magento\Framework\Model\AbstractModel implements \MageSuite\Shipcloud\Api\Data\OrderInterface
{
    protected function _construct()
    {
        $this->_init(\MageSuite\Shipcloud\Model\ResourceModel\Order::class);
    }

    public function getRetryCount()
    {
        return $this->getData(self::RETRY_COUNT);
    }

    public function setRetryCount($retryCount)
    {
        return $this->setData(self::RETRY_COUNT, $retryCount);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
