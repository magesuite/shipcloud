<?php

namespace MageSuite\Shipcloud\Controller\Adminhtml\Order;

class MassRetryExport extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResourceModel;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Order
     */
    protected $shipcloudOrderResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResourceModel,
        \MageSuite\Shipcloud\Model\ResourceModel\Order $shipcloudOrderResource
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->shipcloudOrderResource = $shipcloudOrderResource;
    }

    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        $updatedOrdersCount = 0;

        foreach ($collection->getItems() as $order) {
            if ($order->getData('shipcloud_status') == \MageSuite\Shipcloud\Service\OrderShipmentGenerator::STATUS_GENERATED) {
                continue;
            }

            $this->retrySendToShipcloud($order);
            $updatedOrdersCount++;
        }

        $notUpdatedOrdersCount = $collection->count() - $updatedOrdersCount;

        if ($notUpdatedOrdersCount && $updatedOrdersCount) {
            $this->messageManager->addError(__('%1 order(s) were not updated.', $notUpdatedOrdersCount));
        } elseif ($notUpdatedOrdersCount) {
            $this->messageManager->addError(__('No order(s) were updated.'));
        }

        if ($updatedOrdersCount) {
            $this->messageManager->addSuccess(__('You have updated %1 order(s).', $updatedOrdersCount));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    public function retrySendToShipcloud(\Magento\Sales\Model\Order $order)
    {
        if ($order->getData('shipcloud_status') != \MageSuite\Shipcloud\Service\OrderShipmentGenerator::STATUS_PENDING) {
            $order->setData('shipcloud_status', \MageSuite\Shipcloud\Service\OrderShipmentGenerator::STATUS_PENDING);
            $this->orderResourceModel->saveAttribute($order, 'shipcloud_status');
        }

        if ($this->shipcloudOrderResource->isRetryLimitReached($order)) {
            $this->shipcloudOrderResource->deleteRetryCount($order);
        }
    }
}
