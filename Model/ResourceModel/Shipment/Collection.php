<?php

namespace MageSuite\Shipcloud\Model\ResourceModel\Shipment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\MageSuite\Shipcloud\Model\Shipment::class, \MageSuite\Shipcloud\Model\ResourceModel\Shipment::class);
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
