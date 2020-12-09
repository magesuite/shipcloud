<?php

namespace MageSuite\Shipcloud\Model\ResourceModel;

class Shipment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('shipcloud_shipment', 'entity_id');
    }

    /**
     * @param $orderId
     * @return int
     */
    public function getNumberOfShipments($orderId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from(
                $this->getMainTable(),
                'COUNT(*)'
            )->where(
                'order_id = ?',
                (int)$orderId
            );

        return (int)$adapter->fetchOne($select);
    }

    public function getLastPackageNumber($orderId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from(
                $this->getMainTable(),
                'package_number'
            )->where(
                'order_id = ?',
                (int)$orderId
            )->order('package_number DESC');

        return (int)$adapter->fetchOne($select);
    }
}
