<?php

namespace MageSuite\Shipcloud\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->configuration = $configuration;
        $this->dateTime = $dateTime;
    }

    protected function _construct()
    {
        $this->_init('shipcloud_order', 'order_id');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function incrementRetryCount(\Magento\Sales\Model\Order $order)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                'order_id' => $order->getId(),
                'retry_count' => 1,
                'updated_at' => $this->dateTime->formatDate(true)
            ],
            ['retry_count' => new \Zend\Db\Sql\Expression('retry_count+1'), 'updated_at']
        );

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteRetryCount(\Magento\Sales\Model\Order $order)
    {
        return $this->getConnection()->delete(
            $this->getMainTable(),
            ['order_id = ?' => $order->getId()]
        );
    }

    /**
     * Add retry count limit to order collection
     * Also applied one hour gap between another tries
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addRetryCountLimit(\Magento\Sales\Model\ResourceModel\Order\Collection $collection)
    {
        $limit = $this->configuration->getRetryLimit();
        $date = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $condArray = [
            $this->getConnection()->quoteInto('so.retry_count < ?', $limit),
            $this->getConnection()->quoteInto('so.updated_at <= ?', $this->dateTime->formatDate($date))
        ];
        $conditions = '(' . implode(' AND ', $condArray) . ')';
        $collection->getSelect(
            )->joinLeft(
                ['so' => $this->getMainTable()],
                'so.order_id = main_table.entity_id',
                []
            )->where(
                'so.retry_count IS NULL'
            )->orWhere($conditions);

        return $collection;
    }

    public function isRetryLimitReached(\Magento\Sales\Model\Order $order)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'retry_count')
            ->where('order_id=?', $order->getId());
        $limit = (int)$this->getConnection()->fetchOne($select);

        return $limit >= $this->configuration->getRetryLimit();
    }
}
