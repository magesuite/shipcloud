<?php

namespace MageSuite\Shipcloud\Model\ResourceModel\Order;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\MageSuite\Shipcloud\Model\Order::class, \MageSuite\Shipcloud\Model\ResourceModel\Order::class);
    }

    /**
     * @param $orderId
     * @return Collection
     */
    public function addOrderFilter($orderId)
    {
        return $this->addFieldToFilter('order_id', (int)$orderId);
    }
}
